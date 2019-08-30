<?php

namespace LaravelEnso\Cli\app\Commands\Helpers;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use LaravelEnso\Cli\app\Enums\Menus;
use LaravelEnso\Helpers\app\Classes\Obj;

class Config
{
    private $console;
    private $cliData;

    public function __construct(Command $console, CliData $cliData)
    {
        $this->console = $console;
        $this->cliData = $cliData;
    }

    public function fill($choice)
    {
        if ($this->missesRequired($choice)) {
            return;
        }

        if ($choice === Menus::ToggleValidation) {
            $this->toggleValidation();
            $this->cliData->save();

            return;
        }

        $this->console->info(Str::title($choice).' configuration:');

        $this->displayConfiguration($choice);

        if ($this->console->confirm('Configure '.Str::title($choice))) {
            $this->updateConfiguration($choice);
        }

        $this->cliData->save();
    }

    private function missesRequired($choice)
    {
        $diff = $this->requires($choice)->diff($this->cliData->configured());

        if ($diff->isNotEmpty()) {
            $this->console->warn('You must configure first: '.$diff->implode(', '));
            $this->console->line('');
            sleep(1);
        }

        return $diff->isNotEmpty();
    }

    private function requires($choice)
    {
        return new Obj(config('enso.structures.'.Str::camel($choice).'.requires'));
    }

    private function displayConfiguration($choice)
    {
        $config = $this->cliData->choices()->get(Str::camel($choice));

        $config->keys()->each(function ($key) use ($config) {
            $this->console->line($key.' => '.
                (is_bool($config->get($key))
                    ? Symbol::bool($config->get($key))
                    : $config->get($key))
            );
        });
    }

    private function updateConfiguration($choice)
    {
        $config = $this->cliData->choices()->get(Str::camel($choice));

        $config->keys()->each(function ($key) use ($config) {
            $input = $this->input($config, $key);
            $config->set($key, $input);
        });

        if (! $this->cliData->configured()->contains($choice)) {
            $this->cliData->configured()->push($choice);
        }
    }

    private function input($config, $key)
    {
        $type = gettype($config->get($key));

        $value = is_bool($config->get($key))
            ? $this->console->confirm($key)
            : $this->console->anticipate($key, [$config->get($key) ?? '']);

        if ($this->isValid($type, $value)) {
            return $type === 'integer' ? (int) $value : $value;
        }

        $this->console->error($key.' must be of type '.$type);
        sleep(1);

        return $this->input($config, $key);
    }

    private function isValid($type, $value)
    {
        return $type === 'NULL' || ($type === 'integer' && (string) $value === $value) || (gettype($value) === $type);
    }

    private function toggleValidation(): void
    {
        $this->cliData->toggleValidates();
        $this->console->error('Validation '.($this->cliData->validates() ? 'enabled' : 'disabled'));
        $this->console->line('');
        sleep(1);
    }
}
