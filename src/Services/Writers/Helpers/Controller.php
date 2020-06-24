<?php

namespace LaravelEnso\Cli\Services\Writers\Helpers;

use Illuminate\Support\Str;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Helpers\Services\Obj;

abstract class Controller implements StubProvider
{
    protected Obj $model;
    protected string $group;
    protected string $permission;
    private string $rootSegment;

    public function __construct(Choices $choices, string $permission)
    {
        $this->model = $choices->get('model');
        $this->group = $choices->get('permissionGroup')->get('name');
        $this->permission = $permission;
        $this->rootSegment = $choices->params()->get('rootSegment');
    }

    public function prepare(): void
    {
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        $name = Str::ucfirst($this->permission);

        return $this->path("{$name}.php");
    }

    public function stub(): string
    {
        return Stub::get($this->permission);
    }

    private function path(?string $filename = null): string
    {
        return Path::get([$this->rootSegment, 'Http', 'Controllers'], $filename, true);
    }
}
