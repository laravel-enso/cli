<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\MenuManager\app\Models\Menu;
use LaravelEnso\PermissionManager\app\Models\Permission;
use LaravelEnso\PermissionManager\app\Models\PermissionGroup;
use LaravelEnso\StructureManager\app\Contracts\EnsoStructure;

class Destroyer extends Structure implements EnsoStructure
{
    public function __construct()
    {
        $this->permissions = collect();
    }

    public function handleMenu($menu)
    {
        Menu::whereName($menu)
            ->delete();
    }

    public function handlePermissions($permissions)
    {
        Permission::whereIn('name', array_column($permissions, 'name'))
            ->get()
            ->each
            ->delete();
    }

    public function handlePermissionGroup($permissionGroup)
    {
        $group = PermissionGroup::whereName($permissionGroup['name'])
            ->first();

        if (!$group->permissions()->count()) {
            $group->delete();
        }
    }
}
