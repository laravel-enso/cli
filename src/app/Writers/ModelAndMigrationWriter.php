<?php

namespace LaravelEnso\StructureManager\app\Writers;

use LaravelEnso\Helpers\app\Classes\Obj;

class ModelAndMigrationWriter
{
    private $choices;

    public function __construct(Obj $choices)
    {
        $this->choices = $choices;
    }

    public function run()
    {
        \Artisan::call('make:model', [
            'name'        => $this->choices->get('model')->get('name'),
            '--force'     => true,
            '--migration' => $this->choices->get('files')->has('migration'),
        ]);
    }
}
