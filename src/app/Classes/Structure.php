<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\MenuManager\app\Models\Menu;
use LaravelEnso\PermissionManager\app\Models\PermissionGroup;
use LaravelEnso\StructureManager\app\Exceptions\EnsoStructureException;

abstract class Structure
{
    private const PermissionGroupAttributes = ['name', 'description'];
    private const PermissionAttributes = ['name', 'description', 'type', 'default'];
    private const MenuAttributes = ['name', 'icon', 'link', 'order', 'has_children'];

    protected $permissionGroup = null;
    protected $permissions = null;
    protected $parentMenu = null;
    protected $menu = null;

    public function parentMenu($parentMenu)
    {
        if ($this->validatesParentMenu($parentMenu)) {
            $this->parentMenu = Menu::whereName($parentMenu)
                ->firstOrFail(['id']);
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

    public function permissionGroup($permissionGroup)
    {
        if ($this->validatesPermissionGroup($permissionGroup)) {
            $this->handlePermissionGroup($permissionGroup);
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
            && !empty($menu);
    }

    private function validatesMenu($menu)
    {
        return !is_null($menu)
            && is_array($menu)
            && !empty($menu)
            && $this->validatesStructure(self::MenuAttributes, $menu);
    }

    private function validatesPermissionGroup($permissionGroup)
    {
        return !is_null($permissionGroup)
            && is_array($permissionGroup)
            && !empty($permissionGroup)
            && $this->validatesStructure(self::PermissionGroupAttributes, $permissionGroup);
    }

    private function validatesPermissions($permissions)
    {
        return !is_null($this->permissionGroup)
            && is_array($permissions)
            && !empty($permissions)
            && collect($permissions)
                ->filter(function ($permission) {
                    return !$this->validatesStructure(self::PermissionAttributes, $permission);
                })->isEmpty();
    }

    private function validatesStructure($structure, $attributes)
    {
        $valid = count($structure) === count($attributes)
            && collect($attributes)
                ->keys()
                ->diff(collect($structure)->values())
                ->isEmpty();

        if (!$valid) {
            throw new EnsoStructureException(__(
                'The current structure element is wrongly defined. Check the exception trace below'
            ));
        }

        return $valid;
    }
}
