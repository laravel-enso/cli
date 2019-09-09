<?php

namespace LaravelEnso\Cli\app\Services;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Cli\app\Enums\Options;
use LaravelEnso\Helpers\app\Classes\Obj;

class Choices
{
    private const ProxiedMethods = ['all', 'get', 'put', 'has', 'keys', 'forget'];

    private $console;
    private $choices;
    private $params;
    private $configured;
    private $validates;
    private $validator;

    public function __construct(Command $console)
    {
        $this->console = $console;
        $this->choices = $this->defaults();
        $this->params = $this->attributes('params');
        $this->configured = collect();
        $this->validates = true;
    }

    public function __call($method, $args)
    {
        if (collect(self::ProxiedMethods)->contains($method)) {
            return $this->choices->{$method}(...$args);
        }

        throw new BadMethodCallException('Method '.static::class.'::'.$method.'() not found');
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

    public function validator()
    {
        return $this->validator;
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

    public function setConfigured($configured)
    {
        $this->configured = $configured;

        return $this;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;

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
        if ($this->doesntExist()) {
            return;
        }

        if (! $this->console->confirm('Do you want to restore the last session?')) {
            $this->clear();

            return;
        }

        $this->load();
        $this->console->info('Last session restored');
        $this->console->line('');

        sleep(1);
    }

    public function clear()
    {
        Cache::forget('cli_data');
    }

    public function params()
    {
        return $this->params;
    }

    public function files()
    {
        return $this->choices->get(Str::camel(Options::Files));
    }

    public function hasFiles()
    {
        return $this->choices->filled(Str::camel(Options::Files));
    }

    public function hasError($choice)
    {
        return $this->validator
            && $this->validator->errors()
                ->keys()
                ->contains($choice);
    }

    private function defaults()
    {
        return Options::choices()
            ->reduce(function ($choices, $choice) {
                return $choices->set(
                    Str::camel($choice), $this->attributes($choice)
                );
            }, new Obj);
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

    private function doesntExist()
    {
        return ! Cache::has('cli_data');
    }
}
