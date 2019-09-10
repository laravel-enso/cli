<?php

namespace LaravelEnso\Cli\app\Services\Validators;

use LaravelEnso\Cli\app\Services\Choices;

abstract class Validator
{
    private $errors;

    protected $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
        $this->errors = collect();
    }

    abstract public function run(): self;

    public function fails()
    {
        return $this->errors->isNotEmpty();
    }

    public function errors()
    {
        return $this->errors;
    }

    protected function error($error)
    {
        $this->errors->push($error);
    }
}
