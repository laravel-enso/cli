<?php

namespace LaravelEnso\StructureManager\app\Classes;

use LaravelEnso\MenuManager\app\Models\Menu;
use LaravelEnso\PermissionManager\app\Models\Permission;
use LaravelEnso\RoleManager\app\Models\Role;
use LaravelEnso\StructureManager\app\Contracts\EnsoStructure;

class Creator extends Structure implements EnsoStructure
{
    private $roles;
    private $defaultRole;

    public function __construct()
    {
        $this->permissions = collect();
        $this->roles = Role::pluck('id');
        $this->defaultRole = Role::whereName(config('enso.config.defaultRole'))
            ->pluck('id');
    }

    public function handleMenu($menu)
    {
        $menu['permission_id'] = optional(Permission::whereName($menu['route'])
            ->first())->id;
        unset($menu['route']);

        (Menu::create($menu + [
            'parent_id' => isset($this->parentMenu)
                ? $this->parentMenu->id
                : null,
        ]));
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
        (Permission::create($permission))
            ->roles()
            ->attach(
                $permission['is_default']
                    ? $this->roles
                    : $this->defaultRole
            );
    }
}
