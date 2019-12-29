<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Namespacer;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class Validator implements StubProvider
{
    private Obj $model;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');

        Segments::ucfirst();
        Path::segments();
        Stub::folder('validator');
    }

    public function path(?string $filename = null): string
    {
        return Path::get(['app', 'Http', 'Requests'], $filename);
    }

    public function filename(): string
    {
        return $this->path("Validate{$this->model->get('name')}Request.php");
    }

    public function fromTo(): array
    {
        return [
            '${namespace}' => Namespacer::get(['Http', 'Requests']),
            '${Model}' => Str::ucfirst($this->model->get('name')),
        ];
    }

    public function stub(): string
    {
        return Stub::get('request');
    }
}
