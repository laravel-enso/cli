<?php

namespace LaravelEnso\Cli\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\Services\Writers\Package\Config;
use LaravelEnso\Cli\Services\Writers\Package\Providers;
use LaravelEnso\Cli\Services\Writers\Package\Resources;

class Package implements BulkProvider
{
    public function __construct(private Choices $choices)
    {
        Path::segments(false);
        Stub::folder('package');
    }

    public function collection(): Collection
    {
        $files = new Collection([new Resources($this->choices)]);

        if ($this->choices->get('package')->get('config')) {
            $files->push(new Config($this->choices));
        }

        if ($this->choices->get('package')->get('providers')) {
            $files->push(new Providers($this->choices));
        }

        return $files;
    }
}
