<?php

namespace LaravelEnso\Cli\Services\Writers\Views;

use Illuminate\Support\Str;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Services\Obj;

class View implements StubProvider
{
    private Obj $model;
    private string $permission;

    public function __construct(Choices $choices, string $permission)
    {
        $this->model = $choices->get('model');
        $this->permission = $permission;
    }

    public function prepare(): void
    {
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        $name = Str::ucfirst($this->permission);

        return $this->path("{$name}.vue");
    }

    public function fromTo(): array
    {
        return [
            '${models}' => Str::plural(Str::snake($this->model->get('name'))),
        ];
    }

    public function stub(): string
    {
        return Stub::get($this->permission);
    }

    private function path(?string $filename = null): string
    {
        return Path::get(['client', 'src', 'js', 'pages'], $filename, true);
    }
}
