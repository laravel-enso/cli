<?php

namespace LaravelEnso\Cli\app\Services\Writers\Form;

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
            '${Model}' => $this->model->get('name'),
            '${model}' => lcfirst($this->model->get('name')),
            '${title}' => Str::snake($this->model->get('name'), ' '),
            '${permissionGroup}' => $this->group,
            '${namespace}' => Namespacer::get(['Http', 'Controllers'], true),
            '${modelNamespace}' => $this->model->get('namespace'),
            '${builderNamespace}' => Namespacer::get(['Forms', 'Builders']),
            '${requestNamespace}' => Namespacer::get(['Http', 'Requests']),
            '${request}' => "Validate{$this->model->get('name')}Request",
        ];
    }

    public function stub(): string
    {
        return Stub::get($this->permission);
    }
}
