<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Form\Builder;
use LaravelEnso\Cli\App\Services\Writers\Form\Controllers;
use LaravelEnso\Cli\App\Services\Writers\Form\Template;
use LaravelEnso\Cli\App\Services\Writers\Forms\Validator;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;

class Form implements BulkProvider
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function collection(): Collection
    {
        Segments::ucfirst();
        Path::segments();
        Stub::folder('form');

        return new Collection($this->providers());
    }

    private function providers()
    {
        return [
            new Template($this->choices),
            new Builder($this->choices),
            new Controllers($this->choices),
            new Validator($this->choices),
        ];
    }
}
