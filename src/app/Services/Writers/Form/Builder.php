<?php

namespace LaravelEnso\Cli\App\Services\Writers\Form;

use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Namespacer;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class Builder implements StubProvider
{
    private Obj $model;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');
    }

    public function prepare(): void
    {
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        return $this->path("{$this->model->get('name')}Form.php");
    }

    public function fromTo(): array
    {
        return [
            '${relativePath}' => Segments::get(false)->implode(DIRECTORY_SEPARATOR),
            '${namespace}' => Namespacer::get(['Forms', 'Builders']),
            '${modelNamespace}' => $this->model->get('namespace'),
            '${depth}' => str_repeat('..'.DIRECTORY_SEPARATOR, Segments::count()),
            '${model}' => Str::camel($this->model->get('name')),
            '${Model}' => $this->model->get('name'),
        ];
    }

    public function stub(): string
    {
        return Stub::get('builder');
    }

    private function path(?string $filename = null): string
    {
        return Path::get(['app', 'Forms', 'Builders'], $filename);
    }
}
