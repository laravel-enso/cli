<?php

namespace LaravelEnso\Cli\Services\Writers\Package;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Services\Choices;

class Resources implements BulkProvider
{
    public function __construct(private Choices $choices)
    {
    }

    public function collection(): Collection
    {
        return new Collection([
            new Resource($this->choices, 'README.md'),
            new Resource($this->choices, 'LICENSE'),
            new Resource($this->choices, 'composer.json'),
        ]);
    }
}
