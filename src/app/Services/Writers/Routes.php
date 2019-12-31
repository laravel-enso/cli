<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\App\Services\Writers\Routes\CrudRoutes;
use LaravelEnso\Cli\App\Services\Writers\Routes\SegmentRoutes;

class Routes implements BulkProvider
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function collection(): Collection
    {
        Segments::ucfirst(false);
        Stub::folder('routes');

        return new Collection($this->providers());
    }

    private function providers(): array
    {
        return [
            new CrudRoutes($this->choices),
            new SegmentRoutes($this->choices)
        ];
    }
}
