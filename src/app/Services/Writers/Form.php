<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Contracts\PreparesBulkWriting;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Form\Builder;
use LaravelEnso\Cli\App\Services\Writers\Form\Controllers;
use LaravelEnso\Cli\App\Services\Writers\Form\Template;
use LaravelEnso\Cli\App\Services\Writers\Forms\Validator;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;

class Form implements BulkProvider, PreparesBulkWriting
{
    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
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
