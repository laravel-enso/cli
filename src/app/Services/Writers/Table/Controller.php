<?php

namespace LaravelEnso\Cli\app\Services\Writers\Table;

use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Namespacer;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class Controller implements StubProvider
{
    private Obj $model;
    private string $group;
    private string $permission;

    public function __construct(Choices $choices, string $permission)
    {
        $this->model = $choices->get('model');
        $this->group = $choices->get('permissionGroup')->get('name');
        $this->permission = $permission;
    }

    public function path(?string $filename = null): string
    {
        return Path::get(['app', 'Http', 'Controllers'], $filename, true);
    }

    public function filename(): string
    {
        $name = Str::ucfirst($this->permission);

        return $this->path("{$name}.php");
    }

    public function fromTo(): array
    {
        return [
            '${namespace}' => Namespacer::get(['Http', 'Controllers'], true),
            '${builderNamespace}' => Namespacer::get(['Tables', 'Builders']),
            '${Model}' => $this->model->get('name'),
        ];
    }

    public function stub(): string
    {
        return Stub::get($this->permission);
    }
}
