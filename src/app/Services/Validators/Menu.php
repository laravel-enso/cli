<?php

namespace LaravelEnso\Cli\app\Services\Validators;

use LaravelEnso\Menus\app\Models\Menu as Menus;

class Menu extends Validator
{
    private $menu;

    public function run(): Validator
    {
        if (! $this->choices->has('menu')) {
            return $this;
        }

        $this->menu = $this->choices->get('menu');

        if ($this->menu->filled('route')) {
            $this->validateRoute();
        }

        if (! $this->menu->filled('route') && ! $this->menu->get('has_children')) {
            $this->error('A regular menu must have the route attribute filled');
        }

        if ($this->menu->filled('parentMenu')) {
            $this->validateParentMenu();
        }

        return $this;
    }

    private function validateRoute()
    {
        if ($this->menu->get('has_children')) {
            $this->error('A parent menu must have the route attribute empty');
        }

        $this->validateGroup();

        $this->validatePermission();
    }

    private function validateGroup()
    {
        if ($this->choices->has('permissionGroup')
            && $this->choices->get('permissionGroup')
                ->get('name') !== $this->routeSegments()
                    ->slice(0, -1)->implode('.')) {
            $this->error('The menu\'s route does not match the configured permission group');
        }
    }

    private function validatePermission()
    {
        if (! collect($this->choices->get('permissions')->all())
            ->filter()
            ->keys()
            ->contains($this->routeSegments()->last())) {
            $this->error('The menu\'s route does not match the configured permissions');
        }
    }

    private function validateParentMenu()
    {
        $matches = $this->parentMenuMatches();

        if ($matches === 0) {
            $this->error(
                'The parent menu '.$this->menu->get('parentMenu').' does not exist in the system'
            );
        }

        if ($matches > 1) {
            $this->error(
                "The parent menu {$this->menu->get('parentMenu')} is ambiguous."
                .' Please use dotted notation to specify its parent too.'
            );
        }
    }

    private function parentMenuMatches()
    {
        $parentMenu = collect(explode('.', $this->menu->get('parentMenu')))->pop();

        $parents = Menus::whereName($parentMenu)
            ->whereHasChildren(true)
            ->get();

        return $parents->filter(fn ($menu) => (
                $menu->name === $this->menu->get('parentMenu')
                || $this->nestedParentMatches($menu)
            ))->count();
    }

    private function nestedParentMatches($menu)
    {
        $found = false;
        $nestedMenu = $menu->name;

        while ($menu->parent_id !== null) {
            $parent = $menu->parent;
            $nestedMenu = $parent->name.'.'.$nestedMenu;

            if ($nestedMenu === $this->menu->get('parentMenu')) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    private function routeSegments()
    {
        return collect(explode('.', $this->menu->get('route')));
    }
}
