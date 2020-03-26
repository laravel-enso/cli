<?php

namespace LaravelEnso\Cli\App\Services;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Enums\Options;
use LaravelEnso\Helpers\App\Classes\Obj;

class Choices
{
    private Command $console;
    private Obj $choices;
    private Obj $params;
    private Collection $configured;
    private Collection $errors;
    private bool $validates;

    public function __construct(Command $console)
    {
        $this->console = $console;
        $this->choices = $this->defaults();
        $this->params = $this->attributes('params');
        $this->configured = new Collection();
        $this->errors = new Collection();
        $this->validates = true;
    }

    public function __call($method, $args)
    {
        return $this->choices->{$method}(...$args);
    }

    public function console()
    {
        return $this->console;
    }

    public function configured()
    {
        return $this->configured;
    }

    public function isConfigured()
    {
        return $this->configured->isNotEmpty();
    }

    public function needsValidation()
    {
        return $this->validates;
    }

    public function setChoices(Obj $choices)
    {
        $this->choices = $choices;

        return $this;
    }

    public function setParams(Obj $params)
    {
        $this->params = $params;

        return $this;
    }

    public function setConfigured(array $configured)
    {
        $this->configured = new Collection($configured);

        return $this;
    }

    public function errors(Collection $errors)
    {
        $this->errors = $errors;

        return $this;
    }

    public function toggleValidation()
    {
        $this->validates = ! $this->validates;
        $this->save();

        return $this;
    }

    public function save()
    {
        Cache::put('cli_data', [
            'params' => $this->params,
            'choices' => $this->choices,
            'configured' => $this->configured,
            'validates' => $this->validates,
        ]);

        return $this;
    }

    public function restore()
    {
        if ($this->isNotCached()) {
            return;
        }

        if (! $this->console->confirm('Do you want to restore the last session?')) {
            $this->clearCache();

            return;
        }

        $this->load();

        $this->console->info('Last session restored');
        $this->console->line('');

        sleep(1);
    }

    public function clearCache()
    {
        Cache::forget('cli_data');
    }

    public function params()
    {
        return $this->params;
    }

    public function invalid($choice)
    {
        return $this->errors->keys()->contains($choice);
    }

    private function defaults()
    {
        return Options::choices()
            ->reduce(fn ($choices, $choice) => $choices
                ->set(Str::camel($choice), $this->attributes($choice)), new Obj());
    }

    private function attributes($choice)
    {
        return new Obj(config('enso.structures.'.Str::camel($choice).'.attributes'));
    }

    private function load()
    {
        [
            'params' => $this->params,
            'choices' => $this->choices,
            'configured' => $this->configured,
            'validates' => $this->validates,
        ] = Cache::get('cli_data');
    }

    private function isNotCached()
    {
        return ! Cache::has('cli_data');
    }
}
