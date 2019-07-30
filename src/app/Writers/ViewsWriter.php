<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class ViewsWriter
{
    const PathPrefix = 'js/pages';
    const Operations = ['create', 'edit', 'index', 'show'];

    private $choices;
    private $params;
    private $path;

    public function __construct(Obj $choices, Obj $params)
    {
        $this->choices = $choices;
        $this->params = $params;
    }

    public function run()
    {
        $this->createFolders()
            ->writeViews();
    }

    private function createFolders()
    {
        if (! File::isDirectory($this->path())) {
            File::makeDirectory($this->path(), 0755, true);
        }

        return $this;
    }

    private function writeViews()
    {
        $this->choices->get('permissions')
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

        File::put(
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
        return $this->path()
            .DIRECTORY_SEPARATOR
            .Str::ucfirst($operation).'.vue';
    }

    private function stub($operation)
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'pages'
            .DIRECTORY_SEPARATOR.$operation.'.stub'
        );
    }

    private function path()
    {
        $this->path =
            $this->params->get('root')
                .'resources'
                .DIRECTORY_SEPARATOR
                .self::PathPrefix.DIRECTORY_SEPARATOR
                .collect(
                    explode('.', $this->choices->get('permissionGroup')->get('name'))
                )->implode(DIRECTORY_SEPARATOR);

        return $this->path;
    }
}
