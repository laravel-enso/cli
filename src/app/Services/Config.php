<?php

namespace LaravelEnso\Cli\app\Services;

use Illuminate\Support\Str;
use LaravelEnso\Cli\app\Enums\Options;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Services\Helpers\Symbol;

class Config
{
    private $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function fill($choice)
    {
        if ($this->missesRequired($choice)) {
            return;
        }

        if ($choice === Options::ToggleValidation) {
            $this->toggleValidation();
            $this->choices->save();

            return;
        }

        $this->console()->info(Str::title($choice).' configuration:');

        $this->display($choice);

        if ($this->console()->confirm('Configure '.Str::title($choice))) {
            $this->update($choice);
        }

        $this->choices->save();
    }

    private function missesRequired($choice)
    {
        $diff = $this->requires($choice)->diff($this->choices->configured());

        if ($diff->isNotEmpty()) {
            $this->console()->warn('You must configure first: '.$diff->implode(', '));
            $this->console()->line('');

            sleep(1);
        }

        return $diff->isNotEmpty();
    }

    private function requires($choice)
    {
        return new Obj(config('enso.structures.'.Str::camel($choice).'.requires'));
    }

    private function display($choice)
    {
        $config = $this->choices->get(Str::camel($choice));

        $config->keys()->each(function ($key) use ($config) {
            $this->console()->line($key.' => '.$this->key($key, $config));
        });
    }

    private function key($key, $config)
    {
        return is_bool($config->get($key))
            ? Symbol::bool($config->get($key))
            : $config->get($key);
    }

    private function update($choice)
    {
        $config = $this->choices->get(Str::camel($choice));

        $config->keys()->each(function ($key) use ($config) {
            $input = $this->input($config, $key);
            $config->set($key, $input);
        });

        if (! $this->choices->configured()->contains($choice)) {
            $this->choices->configured()->push($choice);
        }
    }

    private function input($config, $key)
    {
        $type = gettype($config->get($key));

        $value = is_bool($config->get($key))
            ? $this->console()->confirm($key)
            : $this->console()->anticipate($key, [$config->get($key) ?? '']);

        if ($this->isValid($type, $value)) {
            return $type === 'integer' ? (int) $value : $value;
        }

        $this->console()->error($key.' must be of type '.$type);

        sleep(1);

        return $this->input($config, $key);
    }

    private function isValid($type, $value)
    {
        return $type === 'NULL'
            || ($type === 'integer' && (string) $value === $value)
            || (gettype($value) === $type);
    }

    private function toggleValidation(): void
    {
        $this->choices->toggleValidation();
        $this->console()->error('Validation '.($this->choices->needsValidation() ? 'enabled' : 'disabled'));
        $this->console()->line('');

        sleep(1);
    }

    private function console()
    {
        return $this->choices->console();
    }
}
