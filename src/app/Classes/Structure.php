<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\MenuManager\app\Models\Menu;
use LaravelEnso\PermissionManager\app\Models\Permission;
use LaravelEnso\PermissionManager\app\Models\PermissionGroup;

abstract class Structure
{
    protected $permissionGroup = null;
    protected $permissions = null;
    protected $parentMenu = null;
    protected $menu = null;

    public function setPermissionGroup($permissionGroup)
    {
        if (!$permissionGroup || !is_array($permissionGroup) || empty($permissionGroup)) {
            return false;
        }

        $group = PermissionGroup::whereName($permissionGroup['name'])->first();
        $this->permissionGroup = $group ?: new PermissionGroup($permissionGroup);
    }

    public function setPermissions($permissions)
    {
        if (!$this->permissionGroup || !is_array($permissions) || empty($permissions)) {
            return false;
        }

        foreach ($permissions as $permission) {
            $this->permissions->push(new Permission($permission));
        }
    }

    public function setParentMenu($menu)
    {
        if (!$menu) {
            return false;
        }

        $this->parentMenu = Menu::whereName($menu)->first();
    }

    public function setMenu($menu)
    {
        if (!$menu) {
            return false;
        }

        $this->menu = new Menu($menu);

        if ($this->parentMenu) {
            $this->menu->parent_id = $this->parentMenu->id;
        }
    }
}
