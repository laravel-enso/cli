<?php

namespace LaravelEnso\Cli\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Contracts\PreparesBulkWriting;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\Services\Writers\Routes\CrudRoutes;
use LaravelEnso\Cli\Services\Writers\Routes\SegmentRoutes;

class Routes implements BulkProvider, PreparesBulkWriting
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function prepare(): void
    {
        Segments::ucfirst(false);
        Stub::folder('routes');
    }

    public function collection(): Collection
    {
        return new Collection([
            new CrudRoutes($this->choices),
            new SegmentRoutes($this->choices),
        ]);
    }
}
