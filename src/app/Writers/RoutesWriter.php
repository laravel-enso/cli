<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\Cli\app\Services\Choices;

class RoutesWriter
{
    private const PathPrefix = 'js/routes';
    private const Routes = ['create', 'edit', 'index', 'show'];

    private $choices;
    private $path;
    private $segments;
    private $pathPrefix;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;

        $this->pathPrefix = $choices->params()->get('root').'client/src/'.self::PathPrefix;

        $this->segments = collect(
            explode('.', $this->choices->get('permissionGroup')->get('name'))
        );

        $this->path = $choices->params()->get('root').
            'client/src/'.
            self::PathPrefix.'/'
            .$this->segments->implode('/');
    }

    public function handle()
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
            ->filter()
            ->keys()
            ->intersect(self::Routes)
            ->each(fn($permission) => $this->writeCrudRoute($permission));

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
        $title = collect(explode('_', Str::snake($model)))
            ->map(fn($word) => Str::ucfirst($word))
            ->implode(' ');

        $array = [
            '${Model}' => ucfirst($model),
            '${modelTitle}' => $title,
            '${modelsTitle}' => Str::plural($title),
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
        $this->segments->each(fn($segment, $depth) => (
            $this->writeSegmentRoute($segment, $depth)
        ));
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
