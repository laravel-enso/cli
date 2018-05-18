<?php

namespace LaravelEnso\StructureManager\app\Interfaces;

interface EnsoStructure
{
    public function handlePermissionGroup($permissionGroup);

    public function handlePermissions($permissions);

    public function handleMenu($menu);
}
