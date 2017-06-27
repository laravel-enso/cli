<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\MenuManager\app\Models\Menu;
use LaravelEnso\PermissionManager\app\Models\Permission;
use LaravelEnso\PermissionManager\app\Models\PermissionGroup;
use LaravelEnso\RoleManager\app\Models\Role;

class StructureCreator extends Structure
{
    private $parentMenu;
    private $defaultRole;
    private $roles;

    public function __construct()
    {
        $this->permissions = collect();
        $this->parentMenu = null;
        $this->defaultRole = Role::whereName(config('laravel-enso.defaultRole'))->first(['id']);
        $this->roles = Role::get(['id']);
    }

    public function create()
    {
        \DB::transaction(function () {
            $this->createPermissions();
            $this->createMenu();
        });
    }

    public function setPermissionGroup($permissionGroup)
    {
        if (!$permissionGroup || !is_array($permissionGroup) || empty($permissionGroup)) {
            return false;
        }

        $this->permissionGroup = PermissionGroup::whereName($permissionGroup['name'])->first()
            ?: new PermissionGroup($permissionGroup);
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

    private function createPermissions()
    {
        if (!$this->permissionGroup) {
            return;
        }

        if (!$this->permissionGroup->id) {
            $this->permissionGroup->save();
        }

        $this->permissions->each(function ($permission) {
            $this->attachToRoles($permission);
        });
    }

    private function attachToRoles($permission)
    {
        $permission->permission_group_id = $this->permissionGroup->id;
        $permission->save();

        return ($permission->default)
            ? $permission->roles()->attach($this->roles)
            : $permission->roles()->attach($this->defaultRole);
    }

    private function createMenu()
    {
        if (!$this->menu) {
            return;
        }

        $this->menu->save();
        $this->menu->roles()->attach($this->defaultRole);
    }
}
