<?php

namespace LaravelEnso\Cli\Services;

use LaravelEnso\Cli\Enums\Option;

class Status
{
    public function __construct(private Choices $choices)
    {
    }

    public function display()
    {
        $this->currentConfiguration()
            ->willGenerate();

        return $this;
    }

    public function choice()
    {
        return $this->console()
            ->choice('Choose element to configure', Option::values());
    }

    private function currentConfiguration()
    {
        $this->console()->info('Current configuration status:');

        Option::choices()->each(fn ($choice) => $this->console()
            ->line("{$choice} {$this->status($choice)}"));

        return $this;
    }

    private function status(string $choice)
    {
        return $this->choices->invalid($choice)
            ? Symbol::exclamation()
            : Symbol::bool($this->choices->configured()->contains($choice));
    }

    private function willGenerate()
    {
        $files = $this->choices->get('files')->filter()->keys();

        if ($files->isNotEmpty()) {
            $this->console()->newLine();
            $this->console()->info('Will generate:');

            $files->each(fn ($file) => $this->console()->line($file));
        }
    }

    private function console()
    {
        return $this->choices->console();
    }
}
