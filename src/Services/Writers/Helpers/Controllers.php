<?php

namespace LaravelEnso\Cli\Services\Writers\Helpers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Services\Choices;

abstract class Controllers implements BulkProvider
{
    protected Choices $choices;
    protected Collection $permissions;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
        $this->permissions = $choices->get('permissions')->filter()->keys();
    }

    public function collection(): Collection
    {
        return $this->permissions->intersect($this->routes())
            ->reduce(fn ($collection, $permission) => $collection
                ->push($this->create($permission)), new Collection());
    }

    abstract public function create($permission): Controller;

    abstract public function routes(): array;
}
