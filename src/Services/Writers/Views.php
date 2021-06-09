<?php

namespace LaravelEnso\Cli\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Contracts\PreparesBulkWriting;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\Services\Writers\Views\Views as Bulk;

class Views implements BulkProvider, PreparesBulkWriting
{
    public function __construct(private Choices $choices)
    {
    }

    public function prepare(): void
    {
        Segments::ucfirst(false);
        Path::segments();
        Stub::folder('views');
    }

    public function collection(): Collection
    {
        return new Collection([
            new Bulk($this->choices),
        ]);
    }
}
