<?php

namespace LaravelEnso\StructureManager\app\Writers;

use Illuminate\Support\Str;
use LaravelEnso\Helpers\app\Classes\Obj;

class ViewsWriter
{
    const PathPrefix = 'js/pages';
    const Operations = ['create', 'edit', 'index', 'show'];

    private $choices;
    private $path;

    public function __construct(Obj $choices)
    {
        $this->choices = $choices;

        $segments = collect(
            explode('.', $this->choices->get('permissionGroup')->get('name'))
        );

        $this->path = resource_path(self::PathPrefix.'/'.$segments->implode('/'));
    }

    public function run()
    {
        $this->createFolders()
            ->writeViews();
    }

    private function createFolders()
    {
        if (!\File::isDirectory($this->path)) {
            \File::makeDirectory($this->path, 0755, true);
        }

        return $this;
    }

    private function writeViews()
    {
        collect($this->choices->get('permissions'))
            ->filter(function ($chosen, $operation) {
                return $chosen && collect(self::Operations)->contains($operation);
            })->keys()
            ->each(function ($operation) {
                $this->writeView($operation);
            });
    }

    private function writeView($operation)
    {
        [$from, $to] = $this->fromTo();

        \File::put(
            $this->filename($operation),
            str_replace($from, $to, $this->stub($operation))
        );
    }

    private function fromTo()
    {
        $array = [
            '${models}' => Str::plural(Str::snake(
                $this->choices->get('model')->get('name'))
            ),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function filename($operation)
    {
        return $this->path.DIRECTORY_SEPARATOR.Str::ucfirst($operation).'.vue';
    }

    private function stub($operation)
    {
        return \File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'pages'
            .DIRECTORY_SEPARATOR.$operation.'.stub'
        );
    }
}
