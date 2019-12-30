<?php

namespace LaravelEnso\Cli\App\Services\Writers\Form;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Services\Choices;

class Controllers implements BulkProvider
{
    private const Routes = [
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
    ];

    private Choices $choices;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
        $this->permissions = $choices->get('permissions')->filter()->keys();
    }

    public function collection(): Collection
    {
        return $this->permissions->intersect(self::Routes)
            ->reduce(fn ($collection, $permission) => $collection
                ->push(new Controller($this->choices, $permission)), new Collection());
    }
}
