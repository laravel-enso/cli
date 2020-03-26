<?php

namespace LaravelEnso\Cli\App\Services\Validators;

use Illuminate\Support\Collection;

abstract class Validator
{
    private Collection $errors;

    public function __construct()
    {
        $this->errors = new Collection();
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
