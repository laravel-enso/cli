<?php

namespace LaravelEnso\Cli\app\Commands;

use Illuminate\Console\Command;
use LaravelEnso\Cli\app\Enums\Options;
use LaravelEnso\Cli\app\Services\Choices;
use LaravelEnso\Cli\app\Services\Config;
use LaravelEnso\Cli\app\Services\Generator;
use LaravelEnso\Cli\app\Services\Status;

class Cli extends Command
{
    private $choices;

    protected $signature = 'enso:cli';

    protected $description = 'Create a new Laravel Enso Structure';

    public function __construct()
    {
        parent::__construct();

        $this->choices = new Choices($this);
    }

    public function handle()
    {
        $this->info('Create a new Laravel Enso Structure');

        $this->line('');

        $this->choices->restore();

        $this->index();
    }

    private function index()
    {
        $choice = (new Status($this->choices))
            ->display()
            ->choice();

        switch ($choice) {
            case Options::Exit:
                return;
            case Options::Generate:
                return (new Generator($this->choices))->handle()
                    ? null
                    : $this->index();
            default:
                (new Config($this->choices))->fill($choice);

                return $this->index();
        }
    }
}
