<?php

namespace LaravelEnso\Cli\app\Commands\Helpers;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use LaravelEnso\Cli\app\Enums\Menus;
use Illuminate\Support\Facades\Cache;
use LaravelEnso\Helpers\app\Classes\Obj;

class CliData
{
    private $console;
    private $menus;

    private $choices;
    private $params;
    private $configured;
    private $validates;
    private $validator;

    public function __construct(Command $console)
    {
        $this->menus = App::make(Menus::class);
        $this->console = $console;

        $this->setChoices()->setParams();
        $this->configured = collect();
        $this->validates = true;
    }

    public function choices()
    {
        return $this->choices;
    }

    public function configured()
    {
        return $this->configured;
    }

    public function hasConfigured()
    {
        return $this->configured->isNotEmpty();
    }

    public function setConfigured($configured)
    {
        $this->configured = $configured;

        return $this;
    }

    public function validator()
    {
        return $this->validator;
    }

    public function setValidator($validator)
    {
        $this->validator = $validator;

        return $this;
    }

    public function validates()
    {
        return $this->validates;
    }

    public function toggleValidates()
    {
        $this->validates = ! $this->validates;

        return $this;
    }

    public function save()
    {
        Cache::put('cli_data', [
            $this->params, $this->choices, $this->configured, $this->validates,
        ]);

        return $this;
    }

    public function clearSave()
    {
        Cache::forget('cli_data');
    }

    public function handleSavedSession()
    {
        if ($this->hasSave()
            && $this->console->confirm('Do you want to continue the last session?')) {
            $this->loadSave();
            $this->console->info('Last session restored');
            $this->console->line('');
        }
    }

    public function params()
    {
        return $this->params;
    }

    public function files()
    {
        return $this->choices->get(Str::camel(Menus::Files));
    }

    public function hasFiles()
    {
        return $this->choices->has(Str::camel(Menus::Files));
    }

    private function setChoices()
    {
        $this->choices = new Obj();

        $this->menus::choices()->each(function ($choice) {
            $this->choices->set(
                Str::camel($choice),
                $this->attributes($choice)
            );
        });

        return $this;
    }

    private function setParams()
    {
        $this->params = $this->attributes('params');

        return $this;
    }

    private function attributes($choice)
    {
        return new Obj(config('enso.structures.'.Str::camel($choice).'.attributes'));
    }

    private function loadSave()
    {
        [$this->choices, $this->params, $this->configured, $this->validates] =
            Cache::get('cli_data', [null, null, null, null]);
    }

    private function hasSave()
    {
        return Cache::has('cli_data');
    }
}
