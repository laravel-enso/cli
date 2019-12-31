<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class Model implements StubProvider
{
    private Obj $model;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');

        Stub::folder('model');
        Path::segments(false);
    }

    public function path(?string $filename = null): string
    {
        return Path::get([$this->model->get('path')], $filename);
    }

    public function filename(): string
    {
        return "{$this->model->get('name')}.php";
    }

    public function fromTo(): array
    {
        return [
            '${modelNamespace}' => $this->model->get('namespace'),
            '${Model}' => Str::ucfirst($this->model->get('name')),
        ];
    }

    public function stub(): string
    {
        return Stub::get('model');
    }
}
