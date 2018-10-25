<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\MenuManager\app\Models\Menu;
use LaravelEnso\PermissionManager\app\Models\Permission;
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
            ->each(function ($permission) {
                $permission->roles()->detach();
                $permission->delete();
            });
    }
}
