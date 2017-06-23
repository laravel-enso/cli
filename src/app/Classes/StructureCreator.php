<?php

namespace LaravelEnso\Core\app\Classes\StructureManager;

use LaravelEnso\RoleManager\app\Models\Role;

class StructureCreator extends Structure
{
    private $defaultRole;
    private $roles;

    public function __construct()
    {
        $this->defaultRole = Role::whereName(config('laravel-enso.defaultRole'))->first(['id']);
        $this->roles = Role::get(['id']);
        $this->permissions = collect();
    }

    public function create()
    {
        \DB::transaction(function () {
            $this->createPermissions();
            $this->createMenu();
        });
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
