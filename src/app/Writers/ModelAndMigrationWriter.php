<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;
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
        $model = $this->choices->get('model');

        if (! class_exists('App\\'.$model)) {
            Artisan::call('make:model', [
                'name'        => $model->get('namespace').'\\'.$model->get('name'),
                '--migration' => $this->choices->get('files')->has('migration'),
            ]);

            return;
        }

        if ($this->choices->get('files')->has('migration')) {
            Artisan::call('make:migration', [
                'name' => 'create_table_for_'.Str::plural(Str::snake($model)),
            ]);
        }
    }
}
