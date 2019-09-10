<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use LaravelEnso\Cli\app\Services\Choices;

class ModelAndMigrationWriter
{
    private $choices;
    private $model;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function handle()
    {
        $this->model = $this->choices->get('model');

        if (! class_exists($this->modelPath().DIRECTORY_SEPARATOR.ucfirst($this->model->get('name')))) {
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
            $this->modelPath().DIRECTORY_SEPARATOR.ucfirst($this->model->get('name')).'.php',
            str_replace($from, $to, $this->stub())
        );

        return $this;
    }

    private function fromTo()
    {
        $array = [
            '${modelNamespace}' => $this->model->get('namespace'),
            '${Model}' => ucfirst($this->model->get('name')),
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
        if (! File::isDirectory($this->migrationPath())) {
            File::makeDirectory($this->migrationPath(), 0755, true);
        }

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
        return $this->params()->get('root')
            .'database'
            .DIRECTORY_SEPARATOR.'migrations';
    }

    private function modelPath()
    {
        return $this->params()->get('root').'app'
            .DIRECTORY_SEPARATOR.$this->model->get('path');
    }

    private function params()
    {
        return $this->choices->params();
    }
}
