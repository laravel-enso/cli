<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Contracts\PreparesBulkWriting;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\App\Services\Writers\Views\Views as Bulk;

class Views implements BulkProvider, PreparesBulkWriting
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
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
