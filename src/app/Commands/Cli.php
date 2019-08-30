<?php

namespace LaravelEnso\Cli\app\Commands;

use Illuminate\Console\Command;
use LaravelEnso\Cli\app\Enums\Menus;
use LaravelEnso\Cli\app\Commands\Helpers\Config;
use LaravelEnso\Cli\app\Commands\Helpers\Status;
use LaravelEnso\Cli\app\Commands\Helpers\CliData;
use LaravelEnso\Cli\app\Commands\Helpers\Generator;

class Cli extends Command
{
    private $cliData;

    protected $signature = 'enso:cli';

    protected $description = 'Create a new Laravel Enso Structure';

    public function __construct()
    {
        parent::__construct();

        $this->cliData = new CliData($this);
    }

    public function handle()
    {
        $this->info('Create a new Laravel Enso Structure');
        $this->line('');

        $this->cliData->handleSavedSession();

        return $this->index();
    }

    private function index()
    {
        $command = (new Status($this, $this->cliData))
            ->print()
            ->getNewCommand();

        if ($command === Menus::Close) {
            return 0;
        } elseif ($command === Menus::Generate) {
            if ((new Generator($this, $this->cliData))->generate()) {
                return 0;
            }
        } else {
            (new Config($this, $this->cliData))->fill($command);
        }

        return $this->index();
    }
}
