<?php

namespace LaravelEnso\Cli\App\Services\Writers\Routes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class CrudRoute implements StubProvider
{
    private Obj $model;
    private string $group;
    private string $permission;

    public function __construct(Choices $choices, string $permission)
    {
        $this->model = $choices->get('model');
        $this->group = $choices->get('permissionGroup')->get('name');
        $this->permission = $permission;

        Path::segments();
    }

    public function path(?string $filename = null): string
    {
        return Path::get(['client', 'src', 'js', 'routes'], $filename, true);
    }

    public function filename(): string
    {
        return $this->path("{$this->permission}.js");
    }

    public function fromTo(): array
    {
        $title = (new Collection(explode('_', Str::snake($this->model->get('name')))))
            ->map(fn ($word) => Str::ucfirst($word))
            ->implode(' ');

        return [
            '${Model}' => $this->model->get('name'),
            '${depth}' => str_repeat('..'.DIRECTORY_SEPARATOR, Segments::count() + 1),
            '${modelTitle}' => $title,
            '${modelsTitle}' => Str::plural($title),
            '${model}' => Str::camel($this->model->get('name')),
            '${relativePath}' => Segments::get()->implode(DIRECTORY_SEPARATOR),
            '${prefix}' => $this->group,
        ];
    }

    public function stub(): string
    {
        return Stub::get($this->permission);
    }
}
