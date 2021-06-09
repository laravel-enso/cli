<?php

namespace LaravelEnso\Cli\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Contracts\PreparesBulkWriting;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Form\Builder;
use LaravelEnso\Cli\Services\Writers\Form\Controllers;
use LaravelEnso\Cli\Services\Writers\Form\Template;
use LaravelEnso\Cli\Services\Writers\Form\Validator;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;

class Form implements BulkProvider, PreparesBulkWriting
{
    public function __construct(private Choices $choices)
    {
    }

    public function prepare(): void
    {
        Segments::ucfirst();
        Path::segments();
        Stub::folder('form');
    }

    public function collection(): Collection
    {
        return new Collection([
            new Template($this->choices),
            new Builder($this->choices),
            new Controllers($this->choices),
            new Validator($this->choices),
        ]);
    }
}
