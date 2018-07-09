<?php

namespace LaravelEnso\StructureManager\app\Writers;

use LaravelEnso\Helpers\app\Classes\Obj;

class RoutesWriter
{
    const PathPrefix = 'assets/js/routes';
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
        if (!\File::isDirectory($this->path)) {
            \File::makeDirectory($this->path, 0755, true);
        }

        return $this;
    }

    private function writeCrudRoutes()
    {
        collect($this->choices->get('permissions'))
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

        \File::put(
            $this->path.DIRECTORY_SEPARATOR.$permission.'.js',
            str_replace($from, $to, $this->stub($permission))
        );
    }

    private function crudFromTo()
    {
        $group = $this->choices->get('permissionGroup')->get('name');

        $array = [
            '${Model}' => $this->choices->get('model')->get('name'),
            '${relativePath}' => $this->segments->implode('/'),
            '${prefix}' => $group,
            '${depth}' => str_repeat('../', $this->segments->count()),
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

        \File::put(
            $this->pathPrefix.DIRECTORY_SEPARATOR.$segment.'.js',
            str_replace($from, $to, $this->stub($stub))
        );

        $this->pathPrefix .= DIRECTORY_SEPARATOR.$segment;
    }

    private function segmentFromTo($segment, $depth)
    {
        $array = [
            '${segment}' => $segment,
            '${depth}' => str_repeat('../', $depth),
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
        return \File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'routes'
            .DIRECTORY_SEPARATOR.$permission.'.stub'
        );
    }
}
