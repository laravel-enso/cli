<?php

namespace LaravelEnso\Cli\Services;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Enums\Option;

class Validator
{
    private Choices $choices;
    private Collection $errors;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
        $this->errors = new Collection();
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

        if ($validator?->fails()) {
            $this->errors->put($choice, $validator->errors());
        }
    }

    private function validator($choice)
    {
        $validator = Option::from($choice)->validator();

        return $validator ? (new $validator($this->choices))->run() : null;
    }
}
