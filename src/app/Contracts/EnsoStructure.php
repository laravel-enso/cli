<?php

namespace LaravelEnso\StructureManager\app\Contracts;

interface EnsoStructure
{
    public function handlePermissionGroup($permissionGroup);

    public function handlePermissions($permissions);

    public function handleMenu($menu);
}
