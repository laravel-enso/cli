<?php

namespace LaravelEnso\StructureManager\app\Classes;

use Illuminate\Database\Migrations\Migration;

abstract class StructureMigration extends Migration
{
    protected $parentMenu;
    protected $menu;
    protected $permissionGroup;
    protected $permissions;

    public function up()
    {
        \DB::transaction(function () {
            (new Creator())
                ->parentMenu($this->parentMenu)
                ->menu($this->menu)
                ->permissionGroup($this->permissionGroup)
                ->permissions($this->permissions);
        });
    }

    public function down()
    {
        if (config('app.env') == 'testing') {
            return;
        }

        \DB::transaction(function () {
            (new Destroyer())
                ->menu($this->menu)
                ->permissions($this->permissions)
                ->permissionGroup($this->permissionGroup);
        });
    }
}
