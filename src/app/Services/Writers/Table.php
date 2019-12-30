<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use LaravelEnso\Cli\App\Services\BulkWriter;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writer;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\App\Services\Writers\Table\Builder;
use LaravelEnso\Cli\App\Services\Writers\Table\Controllers;
use LaravelEnso\Cli\App\Services\Writers\Table\Template;

class Table
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function handle()
    {
        Segments::ucfirst();
        Path::segments();
        Stub::folder('table');

        (new Writer(new Template($this->choices)))->handle();
        (new Writer(new Builder($this->choices)))->handle();
        (new BulkWriter(new Controllers($this->choices)))->handle();
    }
}
