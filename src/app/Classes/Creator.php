<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\MenuManager\app\Models\Menu;
use LaravelEnso\PermissionManager\app\Models\Permission;
use LaravelEnso\PermissionManager\app\Models\PermissionGroup;
use LaravelEnso\RoleManager\app\Models\Role;
use LaravelEnso\StructureManager\app\Contracts\EnsoStructure;

class Creator extends Structure implements EnsoStructure
{
    private $roles;
    private $defaultRole;

    public function __construct()
    {
        $this->permissions = collect();
        $this->roles = Role::get(['id']);
        $this->defaultRole = Role::whereName(config('enso.config.defaultRole'))
            ->first(['id']);
    }

    public function handleMenu($menu)
    {
        (Menu::create($menu + [
            'parent_id' => isset($this->parentMenu)
                ? $this->parentMenu->id
                : null,
        ]))
        ->roles()
        ->attach($this->defaultRole);
    }

    public function handlePermissionGroup($permissionGroup)
    {
        $this->permissionGroup = PermissionGroup::firstOrCreate(
            ['name' => $permissionGroup['name']],
            $permissionGroup
        );
    }

    public function handlePermissions($permissions)
    {
        collect($permissions)->each(function ($permission) {
            $this->createPermission($permission);
        });

        return $this;
    }

    private function createPermission($permission)
    {
        $permission['permission_group_id'] = $this->permissionGroup->id;

        (Permission::create($permission))
            ->roles()
            ->attach(
                $permission['is_default']
                    ? $this->roles
                    : $this->defaultRole
            );
    }
}
