<?php

namespace LaravelEnso\Cli\app\Services\Writers\Views;

use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class View implements StubProvider
{
    private Obj $model;
    private string $permission;

    public function __construct(Choices $choices, string $permission)
    {
        $this->model = $choices->get('model');
        $this->permission = $permission;
    }

    public function path(?string $filename = null): string
    {
        return Path::get(['client', 'src', 'js', 'pages'], $filename, true);
    }

    public function filename(): string
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
}
