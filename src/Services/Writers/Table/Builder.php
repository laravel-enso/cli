<?php

namespace LaravelEnso\Cli\Services\Writers\Table;

use Illuminate\Support\Str;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Namespacer;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Classes\Obj;

class Builder implements StubProvider
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
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        return $this->path("{$this->model->get('name')}Table.php");
    }

    public function fromTo(): array
    {
        return [
            '${namespace}' => Namespacer::get(['Tables', 'Builders']),
            '${modelNamespace}' => $this->model->get('namespace'),
            '${Model}' => $this->model->get('name'),
            '${models}' => Str::camel(Str::plural($this->model->get('name'))),
            '${table}' => Str::snake(Str::plural($this->model->get('name'))),
            '${depth}' => str_repeat('..'.DIRECTORY_SEPARATOR, Segments::count()),
            '${relativePath}' => Segments::get(false)->implode(DIRECTORY_SEPARATOR),
        ];
    }

    public function stub(): string
    {
        return Stub::get('builder');
    }

    private function path(?string $filename = null): string
    {
        return Path::get([$this->rootSegment, 'Tables', 'Builders'], $filename);
    }
}
