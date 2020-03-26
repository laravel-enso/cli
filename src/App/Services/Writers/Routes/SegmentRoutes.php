<?php

namespace LaravelEnso\Cli\App\Services\Writers\Routes;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;

class SegmentRoutes implements BulkProvider
{
    private Choices $choices;
    private Collection $segments;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
        $this->segments = new Collection();
    }

    public function collection(): Collection
    {
        return Segments::get()
            ->reduce(fn ($collection, $segment) => $collection
                ->push(new SegmentRoute(
                    $this->choices, clone $this->segments->push($segment)
                )), new Collection());
    }
}
