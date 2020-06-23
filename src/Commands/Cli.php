<?php

namespace LaravelEnso\Cli\Commands;

use Illuminate\Console\Command;
use LaravelEnso\Cli\Enums\Options;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Config;
use LaravelEnso\Cli\Services\Generator;
use LaravelEnso\Cli\Services\Status;

class Cli extends Command
{
    protected $signature = 'enso:cli';
    protected $description = 'Create a new Laravel Enso Structure';

    private Choices $choices;

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
        $choice = $this->input();

        switch ($choice) {
            case Options::Exit:
                break;
            case Options::Generate:
                if (! (new Generator($this->choices))->handle()) {
                    $this->index();
                }
                break;
            default:
                (new Config($this->choices))->fill($choice);
                $this->index();
                break;
        }
    }

    private function input()
    {
        return (new Status($this->choices))
            ->display()
            ->choice();
    }
}
