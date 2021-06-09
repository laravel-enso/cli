<?php

namespace LaravelEnso\Cli\Services\Writers\Routes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Services\Obj;

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
    }

    public function prepare(): void
    {
        Path::segments();
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        return $this->path("{$this->permission}.js");
    }

    public function fromTo(): array
    {
        $title = Collection::wrap(explode('_', Str::snake($this->model->get('name'))))
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

    private function path(?string $filename = null): string
    {
        return Path::get(['client', 'src', 'js', 'routes'], $filename, true);
    }
}
