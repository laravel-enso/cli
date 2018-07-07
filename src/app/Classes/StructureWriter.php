<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\StructureManager\app\Writers\FormWriter;
use LaravelEnso\StructureManager\app\Writers\ModelAndMigrationWriter;
use LaravelEnso\StructureManager\app\Writers\RoutesWriter;
use LaravelEnso\StructureManager\app\Writers\SelectWriter;
use LaravelEnso\StructureManager\app\Writers\StructureMigrationWriter;
use LaravelEnso\StructureManager\app\Writers\TableWriter;
use LaravelEnso\StructureManager\app\Writers\ViewsWriter;

class StructureWriter
{
    private $choices;

    public function __construct(Obj $choices)
    {
        $this->choices = $choices;
    }

    public function run()
    {
        $this->structure()
            ->modelAndMigration()
            ->routes()
            ->views()
            ->form()
            ->table()
            ->select();
    }

    private function structure()
    {
        (new StructureMigrationWriter($this->choices))
            ->run();

        return $this;
    }

    private function modelAndMigration()
    {
        if ($this->choices->get('files')->has('model')
            || $this->choices->get('files')->has('migration')) {
            (new ModelAndMigrationWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function routes()
    {
        if ($this->choices->get('files')->has('routes')) {
            (new RoutesWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function views()
    {
        if ($this->choices->get('files')->has('views')) {
            (new ViewsWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function form()
    {
        if ($this->choices->get('files')->has('form')) {
            (new SelectWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function table()
    {
        if ($this->choices->get('files')->has('table')) {
            (new TableWriter($this->choices))
                ->run();
        }

        return $this;
    }

    private function select()
    {
        if ($this->choices->get('files')->has('select')) {
            (new FormWriter($this->choices))
                ->run();
        }
    }
}
