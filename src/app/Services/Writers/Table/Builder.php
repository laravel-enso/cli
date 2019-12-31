<?php

namespace LaravelEnso\Cli\App\Services\Writers\Table;

use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
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

    public function path(?string $filename = null): string
    {
        return Path::get(['app', 'Tables', 'Builders'], $filename);
    }

    public function filename(): string
    {
        return "{$this->model->get('name')}Table.php";
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
}
