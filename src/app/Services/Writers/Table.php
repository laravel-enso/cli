<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\App\Services\Writers\Table\Builder;
use LaravelEnso\Cli\App\Services\Writers\Table\Controllers;
use LaravelEnso\Cli\App\Services\Writers\Table\Template;

class Table implements BulkProvider
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function collection(): Collection
    {
        Segments::ucfirst();
        Path::segments();
        Stub::folder('table');

        return new Collection($this->providers());
    }

    private function providers()
    {
        return [
            new Template($this->choices),
            new Builder($this->choices),
            new Controllers($this->choices)
        ];
    }
}
