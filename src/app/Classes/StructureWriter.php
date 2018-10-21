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
        $this->writeStructure();

        if (! $this->choices->has('files')) {
            return;
        }

        $this->writeModelAndMigration()
            ->writeRoutes()
            ->writeViews()
            ->writeForm()
            ->writeTable()
            ->writeSelect();
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

    private function writeSelect()
    {
        if ($this->choices->get('files')->has('select')) {
            (new SelectWriter($this->choices))
                ->run();
        }

        return $this;
    }
}
