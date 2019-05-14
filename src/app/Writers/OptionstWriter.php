<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class OptionstWriter
{
    private $structure;
    private $segments;
    private $path;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;
    }

    public function run()
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
        $model = $this->structure->get('model');

        $array = [
            '${namespace}' => 'App\\Http\\Controllers\\'.$this->segments()->implode('\\'),
            '${modelNamespace}' => $model->get('namespace'),
            '${Model}'     => $model->get('name'),
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
            .'Select.php';
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
        return $this->path
            ?? $this->path = app_path(
                'Http'.DIRECTORY_SEPARATOR.
                'Controllers'.DIRECTORY_SEPARATOR
                .$this->segments()->implode(DIRECTORY_SEPARATOR)
            );
    }

    private function segments()
    {
        return $this->segments
            ?? $this->segments = collect(
                explode('.', $this->structure->get('permissionGroup')->get('name'))
            )->map(function ($segment) {
                return Str::ucfirst($segment);
            });
    }
}
