<?php

namespace LaravelEnso\Cli\App\Services\Validators;

use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Services\Choices;

class Model extends Validator
{
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
