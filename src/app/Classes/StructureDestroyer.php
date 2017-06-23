<?php

namespace LaravelEnso\Core\app\Classes\StructureManager;

use LaravelEnso\MenuManager\app\Models\Menu;

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

    private function deletePermissions()
    {
        if ($this->permissions && $this->permissions->count()) {
            $this->permissions->each->delete();
        }
    }

    private function deletePermissionGroup()
    {
        if ($this->permissionGroup) {
            if (!$this->permissionGroup->permissions->count()) {
                $this->permissionGroup->delete();
            }
        }
    }

    private function deleteMenu()
    {
        if ($this->menu) {
            $this->menu->delete();
        }
    }
}
