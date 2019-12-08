<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\Cli\app\Services\Choices;

class ViewsWriter
{
    private const PathPrefix = 'js/pages';
    private const Operations = ['create', 'edit', 'index', 'show'];

    private $choices;
    private $path;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
        $this->path = $this->path();
    }

    public function handle()
    {
        $this->createFolders()
            ->writeViews();
    }

    private function createFolders()
    {
        if (! File::isDirectory($this->path)) {
            File::makeDirectory($this->path, 0755, true);
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
        return $this->path
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
        return $this->choices->params()->get('root')
            .'client'
            .DIRECTORY_SEPARATOR
            .'src'
            .DIRECTORY_SEPARATOR
            .self::PathPrefix.DIRECTORY_SEPARATOR
            .collect(
                explode('.', $this->choices->get('permissionGroup')->get('name'))
            )->implode(DIRECTORY_SEPARATOR);
    }
}
