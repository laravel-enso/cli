<?php

namespace LaravelEnso\StructureManager\app\Classes;

use Illuminate\Database\Migrations\Migration;

abstract class StructureMigration extends Migration
{
    protected $parentMenu;
    protected $menu;
    protected $permissions;

    public function up()
    {
        \DB::transaction(function () {
            (new Creator())
                ->parentMenu($this->parentMenu)
                ->permissions($this->permissions)
                ->menu($this->menu);
        });
    }

    public function down()
    {
        \DB::transaction(function () {
            (new Destroyer())
                ->menu($this->menu)
                ->permissions($this->permissions);
        });
    }
}
