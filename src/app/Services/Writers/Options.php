<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Namespacer;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class Options implements StubProvider
{
    private Obj $model;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');
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
        return Path::get(['app', 'Http', 'Controllers'], $filename, true);
    }
}
