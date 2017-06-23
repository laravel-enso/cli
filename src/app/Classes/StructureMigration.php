<?php

namespace LaravelEnso\Core\app\Classes\StructureManager;

use Illuminate\Database\Migrations\Migration;

abstract class StructureMigration extends Migration
{
    protected $permissionGroup;
    protected $permissions;
    protected $parentMenu;
    protected $menu;

    public function up()
    {
        $manager = new StructureCreator();

        $manager->setPermissionGroup($this->permissionGroup);
        $manager->setPermissions($this->permissions);
        $manager->setParentMenu($this->parentMenu);
        $manager->setMenu($this->menu);

        $manager->create();
    }

    public function down()
    {
        $manager = new StructureDestroyer();

        $manager->setPermissionGroup($this->permissionGroup);
        $manager->setPermissions($this->permissions);
        $manager->setMenu($this->menu);

        $manager->destroy();
    }
}
