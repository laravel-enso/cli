<?php

namespace LaravelEnso\Cli\Services\Writers\Routes;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;

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
