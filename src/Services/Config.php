<?php

namespace LaravelEnso\Cli\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\Enums\Options;
use LaravelEnso\Helpers\Services\Obj;

class Config
{
    public function __construct(private Choices $choices)
    {
    }

    public function fill(string $choice)
    {
        if ($this->missesRequired($choice) || $this->togglesValidation($choice)) {
            return;
        }

        $choiceLabel = Str::title($choice);
        $this->console()->info("{$choiceLabel} configuration:");
        $this->display($choice);

        if ($this->console()->confirm("Configure {$choiceLabel}")) {
            $this->update($choice);
        }

        $this->choices->save();
    }

    private function missesRequired(string $choice)
    {
        $diff = $this->required(Str::camel($choice))
            ->diff($this->choices->configured());

        if ($diff->isNotEmpty()) {
            $this->console()->warn('You must configure first: '.$diff->implode(', '));
            $this->console()->newLine();

            sleep(1);
        }

        return $diff->isNotEmpty();
    }

    private function togglesValidation(string $choice)
    {
        if ($choice === Options::ToggleValidation) {
            $this->toggleValidation();

            return true;
        }

        return false;
    }

    private function required(string $choice)
    {
        $structure = Str::camel($choice);

        return new Collection(config("enso.structures.{$structure}.requires"));
    }

    private function display(string $choice)
    {
        $config = $this->config($choice);

        $config->keys()->each(fn ($option) => $this->console()
            ->line("{$option} => {$this->option($config, $option)}"));
    }

    private function option(Obj $config, string $option)
    {
        $value = $config->get($option);

        return is_bool($value) ? Symbol::bool($value) : $value;
    }

    private function update(string $choice)
    {
        $config = $this->config($choice);

        $config->keys()
            ->each(fn ($option) => $config->set($option, $this->input($config, $option)));

        if (! $this->choices->configured()->contains($choice)) {
            $this->choices->configured()->push($choice);
        }
    }

    private function input(Obj $config, string $option)
    {
        $expectedType = gettype($config->get($option));

        $input = is_bool($config->get($option))
            ? $this->console()->confirm($option)
            : $this->console()->anticipate($option, [$config->get($option) ?? '']);

        if ($this->isValid($expectedType, $input)) {
            return $expectedType === 'integer' ? (int) $input : $input;
        }

        $this->console()->error("{$option} must be of type {$expectedType}");

        sleep(1);

        return $this->input($config, $option);
    }

    private function isValid($expectedType, $input)
    {
        return $expectedType === 'NULL'
            || ($expectedType === 'integer' && (string) $input === $input)
            || ($expectedType === gettype($input));
    }

    private function toggleValidation()
    {
        $this->choices->toggleValidation();
        $status = $this->choices->needsValidation() ? 'enabled' : 'disabled';
        $this->console()->error("Validation {$status}");
        $this->console()->newLine();

        sleep(1);
    }

    private function config(string $choice)
    {
        return $this->choices->get(Str::camel($choice));
    }

    private function console()
    {
        return $this->choices->console();
    }
}
