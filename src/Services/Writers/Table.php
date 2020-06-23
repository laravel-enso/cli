<?php

namespace LaravelEnso\Cli\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Contracts\PreparesBulkWriting;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\Services\Writers\Table\Builder;
use LaravelEnso\Cli\Services\Writers\Table\Controllers;
use LaravelEnso\Cli\Services\Writers\Table\Template;

class Table implements BulkProvider, PreparesBulkWriting
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function prepare(): void
    {
        Segments::ucfirst();
        Path::segments();
        Stub::folder('table');
    }

    public function collection(): Collection
    {
        return new Collection([
            new Template($this->choices),
            new Builder($this->choices),
            new Controllers($this->choices),
        ]);
    }
}
