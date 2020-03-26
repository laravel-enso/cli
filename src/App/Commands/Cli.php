<?php

namespace LaravelEnso\Cli\App\Commands;

use Illuminate\Console\Command;
use LaravelEnso\Cli\App\Enums\Options;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Config;
use LaravelEnso\Cli\App\Services\Generator;
use LaravelEnso\Cli\App\Services\Status;

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
