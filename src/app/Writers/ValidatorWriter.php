<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Services\Choices;

class ValidatorWriter
{
    private const Actions = ['store', 'update'];

    private $choices;
    private $segments;
    private $path;
    private $model;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function handle()
    {
        $this->createFolders()
            ->write();
    }

    private function createFolders()
    {
        if (! File::isDirectory($this->path())) {
            File::makeDirectory($this->path(), 0755, true);
        }

        return $this;
    }

    private function write()
    {
        [$from, $to] = $this->fromTo();

        $this->choices->get('permissions')
            ->filter()
            ->keys()
            ->intersect(self::Actions)
            ->each(function ($operation) use ($from, $to) {
                File::put(
                    $this->filename($operation),
                    str_replace($from, $to, $this->stub($operation))
                );
            });
    }

    private function fromTo()
    {
        $this->model = $this->choices->get('model');

        $array = [
            '${namespace}' => $this->params()->get('namespace')
                .'Http\\Requests\\'.$this->segments()->implode('\\'),
            '${Model}' => ucfirst($this->model->get('name')),
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
            .'Validate'.ucfirst($this->model->get('name')).Str::ucfirst($operation)
            .'.php';
    }

    private function stub($file)
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'validator'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function path()
    {
        return $this->path
            ?? $this->path = $this->params()->get('root')
                .'app'.DIRECTORY_SEPARATOR
                .'Http'.DIRECTORY_SEPARATOR
                .'Requests'.DIRECTORY_SEPARATOR
                .$this->segments()->implode(DIRECTORY_SEPARATOR);
    }

    private function segments()
    {
        return $this->segments
            ?? $this->segments = collect(
                explode('.', $this->choices->get('permissionGroup')->get('name'))
            )->map(function ($segment) {
                return Str::ucfirst($segment);
            });
    }

    private function params()
    {
        return $this->choices->params();
    }
}
