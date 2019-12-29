<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class Model implements StubProvider
{
    private Obj $model;
    private Obj $files;
    private ?string $root;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');
        $this->files = $choices->get('files');
        $this->root = $choices->params()->get('root');

        Stub::folder('model');
    }

    public function path(?string $filename = null): string
    {
        return (new Collection([
            $this->root, $this->model->get('path'), $filename,
        ]))->filter()->implode(DIRECTORY_SEPARATOR);
    }

    public function filename(): string
    {
        return $this->path("{$this->model->get('name')}.php");
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
