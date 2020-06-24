<?php

namespace LaravelEnso\Cli\Services\Validators;

use Illuminate\Support\Str;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Helpers\Services\Obj;

class Model extends Validator
{
    private Obj $model;

    public function __construct(Choices $choices)
    {
        parent::__construct();

        $this->model = $choices->get('model');
    }

    public function run(): Validator
    {
        if (Str::contains($this->model->get('name'), '\\')) {
            $this->error('Namespaced models must only use slashes ("/")');
        }

        if (Str::contains($this->model->get('name'), '//')) {
            $this->error('Namespaced models must only use one slash for each segment');
        }

        return $this;
    }
}
