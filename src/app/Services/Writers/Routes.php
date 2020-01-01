<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Contracts\PreparesBulkWriting;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\App\Services\Writers\Routes\CrudRoutes;
use LaravelEnso\Cli\App\Services\Writers\Routes\SegmentRoutes;

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
