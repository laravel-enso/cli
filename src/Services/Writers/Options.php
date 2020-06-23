<?php

namespace LaravelEnso\Cli\Services\Writers;

use Illuminate\Support\Str;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Namespacer;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Classes\Obj;

class Options implements StubProvider
{
    private Obj $model;
    private string $rootSegment;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');
        $this->rootSegment = $choices->params()->get('rootSegment');
    }

    public function prepare(): void
    {
        Segments::ucfirst();
        Path::segments();
        Stub::folder('options');
        Directory::prepare($this->path());
    }

    public function filePath(): string
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

    private function path(?string $filename = null): string
    {
        return Path::get([$this->rootSegment, 'Http', 'Controllers'], $filename, true);
    }
}
