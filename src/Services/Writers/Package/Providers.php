<?php

namespace LaravelEnso\Cli\Services\Writers\Package;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Services\Choices;

class Providers implements BulkProvider
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function collection(): Collection
    {
        return new Collection([
            new Provider($this->choices, 'AppServiceProvider.php'),
            new Provider($this->choices, 'AuthServiceProvider.php')
        ]);
    }
}
