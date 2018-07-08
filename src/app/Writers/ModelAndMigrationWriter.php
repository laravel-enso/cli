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
        $model = $this->choices->get('model')->get('name');

        if (!class_exists('App\\'.$model)) {
            \Artisan::call('make:model', [
                'name' => $this->choices->get('model')->get('name'),
                '--migration' => $this->choices->get('files')->has('migration'),
            ]);

            return;
        }

        if ($this->choices->get('files')->has('migration')) {
            \Artisan::call('make:migration', [
                'name' => 'create_table_for_'.strplural(snake_case($model)),
            ]);
        }
    }
}
