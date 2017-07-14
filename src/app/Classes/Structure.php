<?php

namespace LaravelEnso\StructureManager\app\Classes;

abstract class Structure
{
    protected $permissionGroup = null;
    protected $permissions = null;
    protected $parentMenu = null;
    protected $menu = null;

    abstract public function setPermissionGroup($permissionGroup);

    abstract public function setPermissions($permissions);

    abstract public function setMenu($menu);
}
