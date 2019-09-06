<?php

namespace LaravelEnso\Cli\app\Services\Validators;

use Illuminate\Support\Str;

class Model extends Validator
{
    public function run(): Validator
    {
        $model = $this->choices->get('model')->get('name');

        if (Str::contains($model, '\\')) {
            $this->error('Namespaced models must only use slashes ("/")');
        }

        if (Str::contains($model, '//')) {
            $this->error('Namespaced models must only use one slash for each segment');
        }

        return $this;
    }
}