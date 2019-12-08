<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\Cli\app\Services\Choices;

class ValidatorWriter
{
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

        File::put(
            $this->filename(), str_replace($from, $to, $this->stub())
        );
    }

    private function fromTo()
    {
        $this->model = $this->choices->get('model');
        $baseNamespace = $this->params()->get('namespace').'Http\\Requests';

        $array = [
            '${namespace}' => $this->segments()->prepend($baseNamespace)->implode('\\'),
            '${Model}' => ucfirst($this->model->get('name')),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function filename()
    {
        return $this->path()
            .DIRECTORY_SEPARATOR
            .'Validate'.ucfirst($this->model->get('name')).'Request.php';
    }

    private function stub()
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'validator'
            .DIRECTORY_SEPARATOR.'request.stub'
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
        if (! $this->segments) {
            $this->segments = collect(
                explode('.', $this->choices->get('permissionGroup')->get('name'))
            );

            $this->segments->pop();

            $this->segments = $this->segments->map(function ($segment) {
                return Str::ucfirst($segment);
            });
        }

        return $this->segments;
    }

    private function params()
    {
        return $this->choices->params();
    }
}
