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
        $this->console()->info('Current configuration status:');

        Options::choices()->each(function ($choice) {
            $this->console()->line($choice.' '.(
                $this->choices->hasError($choice)
                    ? Symbol::exclamation()
                    : Symbol::bool($this->choices->configured()->contains($choice))
                ));
        });

        if ($this->choices->configured()->isNotEmpty()) {
            $this->console()->line('');
            $this->console()->info('Will generate:');
            $this->console()->line('structure migration');

            if ($this->choices->hasFiles()) {
                $this->choices->files()->each(function ($chosen, $file) {
                    if ($chosen) {
                        $this->console()->line($file);
                    }
                });
            }
        }

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
}
