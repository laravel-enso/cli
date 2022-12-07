<?php

namespace LaravelEnso\Cli\Commands;

use Illuminate\Console\Command;
use LaravelEnso\Cli\Enums\Option;
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
        $this->newLine();

        $this->choices->restore();

        $this->index();
    }

    private function index()
    {
        $choice = Option::from($this->input());

        if ($choice === Option::Generate) {
            if (! (new Generator($this->choices))->handle()) {
                $this->index();
            }
        } elseif ($choice !== Option::Exit) {
            (new Config($this->choices))->fill($choice->value);
            $this->index();
        }
    }

    private function input()
    {
        return (new Status($this->choices))
            ->display()
            ->choice();
    }
}
