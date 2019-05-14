<?php

namespace LaravelEnso\Cli\app\Services;

use Illuminate\Support\Str;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Writers\FormWriter;
use LaravelEnso\Cli\app\Writers\TableWriter;
use LaravelEnso\Cli\app\Writers\ViewsWriter;
use LaravelEnso\Cli\app\Writers\RoutesWriter;
use LaravelEnso\Cli\app\Writers\OptionstWriter;
use LaravelEnso\Cli\app\Writers\ModelAndMigrationWriter;
use LaravelEnso\Cli\app\Writers\StructureMigrationWriter;

class Structure
{
    private $choices;

    public function __construct(Obj $choices)
    {
        $this->choices = $choices;
        $this->prepareModel();
    }

    public function handle()
    {
        $this->writeStructure();

        if (! $this->choices->has('files')) {
            return;
        }

        $this->writeModelAndMigration()
            ->writeRoutes()
            ->writeViews()
            ->writeForm()
            ->writeTable()
            ->writeOptions();
    }

    private function writeStructure()
    {
        (new StructureMigrationWriter($this->choices))
            ->run();

        return $this;
    }

    private function writeModelAndMigration()
    {
        if ($this->choices->get('files')->has('model')
            || $this->choices->get('files')->has('table migration')) {
            (new ModelAndMigrationWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function writeRoutes()
    {
        if ($this->choices->get('files')->has('routes')) {
            (new RoutesWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function writeViews()
    {
        if ($this->choices->get('files')->has('views')) {
            (new ViewsWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function writeForm()
    {
        if ($this->choices->get('files')->has('form')) {
            (new FormWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function writeTable()
    {
        if ($this->choices->get('files')->has('table')) {
            (new TableWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function writeOptions()
    {
        if ($this->choices->get('files')->has('options')) {
            (new OptionstWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function prepareModel()
    {
        $model = $this->choices->get('model');

        if (! Str::contains($model->get('name'), '\\\\')) {
            $model->set('namespace', 'App');

            return;
        }

        $segments = collect(explode('\\\\', $model->get('name')));
        $model->set('name', $segments->pop());
        $model->set('namespace', $segments->implode('\\'));
    }
}
