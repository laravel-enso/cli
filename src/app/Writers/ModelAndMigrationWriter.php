<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use LaravelEnso\Helpers\app\Classes\Obj;

class ModelAndMigrationWriter
{
    private $choices;
    private $params;
    private $model;

    public function __construct(Obj $choices, Obj $params)
    {
        $this->choices = $choices;
        $this->params = $params;
    }

    public function run()
    {
        $this->model = $this->choices->get('model');

        if (! class_exists($this->modelPath().DIRECTORY_SEPARATOR.$this->model->get('name'))) {
            $this->writeModel()
                ->writeMigration();

            return;
        }

        $this->writeMigration();
    }

    private function writeModel()
    {
        [$from, $to] = $this->fromTo();

        if (! File::isDirectory($this->modelPath())) {
            File::makeDirectory($this->modelPath(), 0755, true);
        }

        File::put(
            $this->modelPath().DIRECTORY_SEPARATOR.$this->model->get('name').'.php',
            str_replace($from, $to, $this->stub())
        );

        return $this;
    }

    private function fromTo()
    {
        $array = [
            '${modelNamespace}' => $this->model->get('namespace'),
            '${Model}' => $this->model->get('name'),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function stub()
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR
            .'stubs'.DIRECTORY_SEPARATOR
            .'models'.DIRECTORY_SEPARATOR
            .'model.stub'
        );
    }

    private function writeMigration()
    {
        if ($this->choices->get('files')->has('migration')) {
            Artisan::call('make:migration', [
                'name' => 'create_'.Str::plural(Str::snake($this->model->get('name'))).'_table',
                '--path' => $this->migrationPath(),
            ]);
        }

        return $this;
    }

    private function migrationPath()
    {
        return $this->params->get('root')
            .'database'
            .DIRECTORY_SEPARATOR
            .'migrations';
    }

    private function modelPath()
    {
        if ($this->choices->get('package')->get('name')) {
            return $this->params->get('root')
                .DIRECTORY_SEPARATOR
                .'app'
                .DIRECTORY_SEPARATOR
                .'Models';
        }

        return 'app'.DIRECTORY_SEPARATOR.'Models';
    }
}
