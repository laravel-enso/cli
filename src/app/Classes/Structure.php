<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\MenuManager\app\Models\Menu;
use LaravelEnso\StructureManager\app\Exceptions\EnsoStructureException;

abstract class Structure
{
    private const PermissionAttributes = ['name', 'description', 'type', 'is_default'];
    private const MenuAttributes = ['name', 'icon', 'route', 'order_index', 'has_children'];

    protected $permissions = null;
    protected $parentMenu = null;
    protected $menu = null;

    public function parentMenu($parentMenu)
    {
        if ($this->validatesParentMenu($parentMenu)) {
            $segments = collect(explode('.', $parentMenu));

            $this->parentMenu = Menu::whereName($segments->pop())
                ->whereHasChildren(true)
                ->get()
                ->first(function ($menu) use ($segments) {
                    return $segments->reverse()
                        ->reduce(function ($match, $segment) {
                            return ! is_null($match) && $match->parent->name === $segment
                                ? $match->parent
                                : null;
                        }, $menu) !== null;
                });
        }

        return $this;
    }

    public function menu($menu)
    {
        if ($this->validatesMenu($menu)) {
            $this->handleMenu($menu);
        }

        return $this;
    }

    public function permissions($permissions)
    {
        if ($this->validatesPermissions($permissions)) {
            $this->handlePermissions($permissions);
        }

        return $this;
    }

    private function validatesParentMenu($menu)
    {
        return is_string($menu)
            && ! empty($menu);
    }

    private function validatesMenu($menu)
    {
        return ! is_null($menu)
            && is_array($menu)
            && ! empty($menu)
            && $this->validatesStructure(self::MenuAttributes, $menu);
    }

    private function validatesPermissions($permissions)
    {
        return is_array($permissions)
            && ! empty($permissions)
            && collect($permissions)
                ->filter(function ($permission) {
                    return ! $this->validatesStructure(self::PermissionAttributes, $permission);
                })->isEmpty();
    }

    private function validatesStructure($structure, $attributes)
    {
        $valid = count($structure) === count($attributes)
            && collect($attributes)
                ->keys()
                ->diff(collect($structure)->values())
                ->isEmpty();

        if (! $valid) {
            throw new EnsoStructureException(__(
                'The current structure element is wrongly defined. Check the exception trace below'
            ));
        }

        return $valid;
    }
}
