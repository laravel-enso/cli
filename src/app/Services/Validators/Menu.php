<?php

namespace LaravelEnso\Cli\App\Services\Validators;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Helpers\App\Classes\Obj;
use LaravelEnso\Menus\App\Models\Menu as Model;

class Menu extends Validator
{
    private ?Obj $menu;
    private Obj $permissions;

    public function __construct(Choices $choices)
    {
        parent::__construct();

        $this->menu = $choices->get('menu');
        $this->permissions = $choices->get('permissions')->filter()->keys();
    }

    public function run(): Validator
    {
        if ($this->menu !== null) {
            $this->menu();

            if ($this->menu->filled('parentMenu')) {
                $this->parent();
            }
        }

        return $this;
    }

    private function menu()
    {
        if (! $this->menu->filled('route')) {
            if (! $this->menu->get('has_children')) {
                $this->error('A regular menu must have the route attribute filled');
            }

            return;
        }

        if ($this->menu->get('has_children')) {
            $this->error('A parent menu must have the route attribute empty');
        }

        if ($this->invalidPermission()) {
            $this->error("The menu's route does not match the configured permissions");
        }
    }

    private function invalidPermission()
    {
        return ! $this->permissions->contains($this->menu->get('route'));
    }

    private function parent()
    {
        $matchCount = $this->parentMatchCount();

        if ($matchCount === 1) {
            return;
        }

        $error = "The parent menu {$this->menu->get('parentMenu')} ";

        $error .= $matchCount === 0
            ? 'does not exist in the system'
            : 'is ambiguous. Please use dotted notation to specify its parent too.';

        $this->error($error);
    }

    private function parentMatchCount()
    {
        $segments = new Collection(explode('.', $this->menu->get('parentMenu')));

        return Model::whereName($segments->pop())
            ->whereHasChildren(true)
            ->get()
            ->filter(fn ($menu) => $this->parentMatches($menu))
            ->count();
    }

    private function parentMatches($menu)
    {
        return $menu->name === $this->menu->get('parentMenu')
            || $this->nestedParentMatches($menu);
    }

    private function nestedParentMatches($menu)
    {
        $matches = false;
        $nestedMenu = $menu->name;

        while (! $matches && $menu->parent_id !== null) {
            $nestedMenu = "{$menu->parent->name}.{$nestedMenu}";
            $matches = $nestedMenu === $this->menu->get('parentMenu');
        }

        return $matches;
    }
}
