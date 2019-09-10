<?php

namespace LaravelEnso\Cli\app\Services;

use LaravelEnso\Cli\app\Enums\Options;
use LaravelEnso\Cli\app\Services\Helpers\Symbol;

class Status
{
    private $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function display()
    {
        $this->currentConfiguration();
        $this->willGenerate();

        return $this;
    }

    public function choice()
    {
        return $this->console()->choice(
            'Choose element to configure', Options::keys()->toArray()
        );
    }

    private function console()
    {
        return $this->choices->console();
    }

    private function currentConfiguration()
    {
        $this->console()->info('Current configuration status:');

        Options::choices()->each(function ($choice) {
            $this->console()->line($choice.' '.(
                $this->choices->hasError($choice)
                    ? Symbol::exclamation()
                    : Symbol::bool($this->choices->configured()->contains($choice))
                ));
        });
    }

    private function willGenerate()
    {
        if ($this->choices->configured()->isNotEmpty()) {
            $this->console()->line('');
            $this->console()->info('Will generate:');
            $this->console()->line('structure migration');

            $this->choices->files()
                ->filter()
                ->keys()
                ->each(function ($file) {
                    $this->console()->line($file);
                });
        }
    }
}
