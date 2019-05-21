<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class RoutesWriter
{
    const PathPrefix = 'js/routes';
    const Operations = ['create', 'edit', 'index', 'show'];

    private $choices;
    private $path;
    private $segments;
    private $pathPrefix;

    public function __construct(Obj $choices)
    {
        $this->choices = $choices;
        $this->pathPrefix = resource_path(self::PathPrefix);

        $this->segments = collect(
            explode('.', $this->choices->get('permissionGroup')->get('name'))
        );

        $this->path = resource_path(
            self::PathPrefix.'/'
            .$this->segments->implode('/')
        );
    }

    public function run()
    {
        $this->createFolders()
            ->writeCrudRoutes()
            ->writeSegmentRoutes();
    }

    private function createFolders()
    {
        if (! File::isDirectory($this->path)) {
            File::makeDirectory($this->path, 0755, true);
        }

        return $this;
    }

    private function writeCrudRoutes()
    {
        $this->choices->get('permissions')
            ->filter(function ($chosen, $permission) {
                return $chosen && collect(self::Operations)->contains($permission);
            })->keys()
            ->each(function ($permission) {
                $this->writeCrudRoute($permission);
            });

        return $this;
    }

    private function writeCrudRoute($permission)
    {
        [$from, $to] = $this->crudFromTo();

        File::put(
            $this->path.DIRECTORY_SEPARATOR.$permission.'.js',
            str_replace($from, $to, $this->stub($permission))
        );
    }

    private function crudFromTo()
    {
        $group = $this->choices->get('permissionGroup')->get('name');
        $model = $this->choices->get('model')->get('name');
        $title = collect(explode('_', Str::snake($model)))->map(function ($word) {
            return Str::ucfirst(Str::plural($word));
        })->implode(' ');

        $array = [
            '${Model}' => $model,
            '${title}' => $title,
            '${model}' => Str::camel($model),
            '${relativePath}' => $this->segments->implode('/'),
            '${prefix}' => $group,
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function writeSegmentRoutes()
    {
        $this->segments->each(function ($segment, $depth) {
            $this->writeSegmentRoute($segment, $depth);
        });
    }

    private function writeSegmentRoute($segment, $depth)
    {
        [$from, $to] = $this->segmentFromTo($segment, $depth);

        $stub = $depth === $this->segments->count() - 1
            ? 'parentSegment'
            : 'segment';

        File::put(
            $this->pathPrefix.DIRECTORY_SEPARATOR.$segment.'.js',
            str_replace($from, $to, $this->stub($stub))
        );

        $this->pathPrefix .= DIRECTORY_SEPARATOR.$segment;
    }

    private function segmentFromTo($segment, $depth)
    {
        $array = [
            '${segment}' => $segment,
            '${breadcrumb}' => collect(explode('_', Str::snake($segment)))->implode(' '),
            '${permissionGroup}' => $this->choices->get('permissionGroup')->get('name'),
            '${relativePath}' => $depth === 0 ?
                '/'.$segment
                : $segment,
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function stub($permission)
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'routes'
            .DIRECTORY_SEPARATOR.$permission.'.stub'
        );
    }
}
