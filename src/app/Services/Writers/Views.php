<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use LaravelEnso\Cli\App\Services\BulkWriter;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\app\Services\Writers\Views\Views as Bulk;

class Views
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function handle()
    {
        Segments::ucfirst(false);
        Path::segments();
        Stub::folder('views');

        (new BulkWriter(new Bulk($this->choices)))->handle();
    }
}
