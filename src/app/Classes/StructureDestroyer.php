<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\MenuManager\app\Models\Menu;
use LaravelEnso\PermissionManager\app\Models\Permission;
use LaravelEnso\PermissionManager\app\Models\PermissionGroup;

class StructureDestroyer extends Structure
{
    public function __construct()
    {
        $this->permissions = collect();
    }

    public function destroy()
    {
        \DB::transaction(function () {
            $this->deletePermissions();
            $this->deletePermissionGroup();
            $this->deleteMenu();
        });
    }

    public function setPermissionGroup($permissionGroup)
    {
        if (is_null($permissionGroup) || !is_array($permissionGroup) || empty($permissionGroup)) {
            return false;
        }

        $this->permissionGroup = PermissionGroup::whereName($permissionGroup['name'])->first();
    }

    public function setPermissions($permissions)
    {
        if (!$this->permissionGroup || !is_array($permissions) || empty($permissions)) {
            return false;
        }

        $permissionNames = array_column($permissions, 'name');
        $this->permissions = Permission::whereIn('name', $permissionNames)->get();
    }

    public function setMenu($menu)
    {
        if (is_null($menu) || !is_array($menu) || empty($menu)) {
            return false;
        }

        $this->menu = Menu::whereName($menu)->first();
    }

    private function deletePermissions()
    {
        if ($this->permissions && $this->permissions->count()) {
            $this->permissions->each->delete();
        }
    }

    private function deletePermissionGroup()
    {
        if ($this->permissionGroup && !$this->permissionGroup->permissions()->count()) {
            $this->permissionGroup->delete();
        }
    }

    private function deleteMenu()
    {
        if ($this->menu) {
            $this->menu->delete();
        }
    }
}
