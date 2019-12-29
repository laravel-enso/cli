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

class Options implements StubProvider
{
    private Obj $model;
    private string $group;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');
        $this->group = $choices->get('permissionGroup')->get('name');

        Segments::ucfirst();
        Path::segments();
        Stub::folder('options');
    }

    public function path(?string $filename = null): string
    {
        return Path::get(['app', 'Http', 'Controllers'], $filename, true);
    }

    public function filename(): string
    {
        return $this->path('Options.php');
    }

    public function fromTo(): array
    {
        return [
            '${namespace}' => Namespacer::get(['Http', 'Controllers'], true),
            '${modelNamespace}' => $this->model->get('namespace'),
            '${Model}' => Str::ucfirst($this->model->get('name')),
        ];
    }

    public function stub(): string
    {
        return Stub::get('controller');
    }
}
