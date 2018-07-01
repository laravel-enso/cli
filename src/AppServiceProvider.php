<?php

namespace LaravelEnso\StructureManager;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\StructureManager\app\Commands\MakeEnsoStructure;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            MakeEnsoStructure::class,
        ]);

        $this->mergeConfigFrom(__DIR__.'/config/model.php', 'enso.structures.model');
        $this->mergeConfigFrom(__DIR__.'/config/menu.php', 'enso.structures.menu');
        $this->mergeConfigFrom(__DIR__.'/config/permissionGroup.php', 'enso.structures.permissionGroup');
        $this->mergeConfigFrom(__DIR__.'/config/permissions.php', 'enso.structures.permissions');
        $this->mergeConfigFrom(__DIR__.'/config/files.php', 'enso.structures.files');
    }

    public function register()
    {
        //
    }
}
