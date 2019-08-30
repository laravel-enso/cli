<?php

namespace LaravelEnso\Cli\app\Commands\Helpers;

use Illuminate\Console\Command;
use LaravelEnso\Cli\app\Enums\Menus;

class Status
{
    private $console;
    private $cliData;

    public function __construct(Command $console, CliData $cliData)
    {
        $this->console = $console;
        $this->cliData = $cliData;
    }

    public function print()
    {
        $this->console->info('Current configuration status:');

        Menus::choices()->each(function ($choice) {
            $this->console->line($choice.' '.(
                $this->hasError($choice)
                    ? Symbol::exclamation()
                    : Symbol::bool($this->cliData->configured()->contains($choice))
                ));
        });

        if ($this->cliData->configured()->isNotEmpty()) {
            $this->console->line('');
            $this->console->info('Will generate:');
            $this->console->line('structure migration');
            $this->cliData->files()->each(function ($chosen, $file) {
                if ($chosen) {
                    $this->console->line($file);
                }
            });
        }

        return $this;
    }

    public function getNewCommand()
    {
        return $this->console->choice('Choose element to configure',
            Menus::keys()->toArray());
    }

    private function hasError($choice)
    {
        return $this->cliData->validator()
            && $this->cliData->validator()->errors()
                ->keys()
                ->contains($choice);
    }
}
