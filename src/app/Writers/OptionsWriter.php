<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\Cli\app\Services\Choices;

class OptionsWriter
{
    private $path;
    private $choices;
    private $segments;

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
            $this->filename(),
            str_replace($from, $to, $this->stub('controller'))
        );
    }

    private function fromTo()
    {
        $model = $this->choices->get('model');

        $array = [
            '${namespace}' => $this->params()->get('namespace')
                .'Http\\Controllers\\'
                .$this->segments()->implode('\\'),
            '${modelNamespace}' => $model->get('namespace'),
            '${Model}' => ucfirst($model->get('name')),
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
            .'Options.php';
    }

    private function stub($file)
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'options'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function path()
    {
        return $this->path ??= $this->params()->get('root')
            .'app'.DIRECTORY_SEPARATOR
            .'Http'.DIRECTORY_SEPARATOR
            .'Controllers'.DIRECTORY_SEPARATOR
            .$this->segments()->implode(DIRECTORY_SEPARATOR);
    }

    private function segments()
    {
        return $this->segments ??= collect(
                explode('.', $this->choices->get('permissionGroup')->get('name'))
            )->map(fn($segment) => Str::ucfirst($segment));
    }

    private function params()
    {
        return $this->choices->params();
    }
}
