<?php

namespace LaravelEnso\Cli\Services\Writers;

use Illuminate\Support\Str;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Services\Obj;

class Model implements StubProvider
{
    private Obj $model;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');
    }

    public function prepare(): void
    {
        Stub::folder('model');
        Path::segments(false);
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        return $this->path("{$this->model->get('name')}.php");
    }

    public function fromTo(): array
    {
        return [
            '${modelNamespace}' => $this->model->get('namespace'),
            '${Model}'          => Str::ucfirst($this->model->get('name')),
        ];
    }

    public function stub(): string
    {
        return Stub::get('model');
    }

    private function path(?string $filename = null): string
    {
        return Path::get([$this->model->get('path')], $filename);
    }
}
