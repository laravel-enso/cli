<?php

namespace LaravelEnso\Cli\app\Services;

use LaravelEnso\Cli\app\Enums\Validators;

class Validator
{
    private $choices;
    private $errors;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
        $this->errors = collect();
    }

    public function run()
    {
        $this->choices->configured()
            ->each(fn ($choice) => $this->validate($choice));

        return $this;
    }

    public function fails()
    {
        return $this->errors->isNotEmpty();
    }

    public function errors()
    {
        return $this->errors;
    }

    private function validate($choice)
    {
        $validator = $this->validator($choice);

        if (optional($validator)->fails()) {
            $this->errors->put($choice, $validator->errors());
        }
    }

    private function validator($choice)
    {
        $validator = Validators::get($choice);

        return $validator ? (new $validator($this->choices))->run() : null;
    }
}
