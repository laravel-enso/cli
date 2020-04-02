<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\App\Services\Writers\Helpers\EnsoStructure\Mapping;
use LaravelEnso\Cli\App\Services\Writers\Helpers\EnsoStructure\Permissions;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class EnsoStructure implements StubProvider
{
    private ?Obj $model;
    private ?Obj $menu;
    private ?Collection $permissions;
    private string $group;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');
        $this->menu = $choices->get('menu');
        $this->group = $choices->get('permissionGroup')->get('name');
        $this->permissions = $choices->has('permissions')
            ? $choices->get('permissions')->filter()->keys()
            : null;
    }

    public function prepare(): void
    {
        Path::segments(false);
        Stub::folder('structure');
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        $timestamp = Carbon::now()->format('Y_m_d_His');
        $structure = Str::snake(Str::plural($this->entity()));

        return $this->path("{$timestamp}_create_structure_for_{$structure}.php");
    }

    public function fromTo(): array
    {
        $mapping = (new Mapping($this->menu, $this->group));

        return [
            '${Entity}' => Str::plural($this->entity()),
            '${menu}' => $mapping->menu(),
            '${parentMenu}' => $mapping->parentMenu(),
            '${permissions}' => (new Permissions(
                $this->model, $this->permissions, $this->group
            ))->get(),
        ];
    }

    public function stub(): string
    {
        return Stub::get('migration');
    }

    private function entity(): string
    {
        return $this->model
            ? Str::ucfirst($this->model->get('name'))
            : Str::ucfirst($this->menu->get('name'));
    }

    private function path(?string $filename = null): string
    {
        return Path::get(['database', 'migrations'], $filename);
    }
}
