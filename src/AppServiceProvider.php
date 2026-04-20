<?php

namespace LaravelEnso\Cli;

use Illuminate\Support\ServiceProvider;
use LaravelEnso\Cli\Commands\Cli;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->load()
            ->publish()
            ->commands(Cli::class);
    }

    private function load()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/model.php', 'enso.structures.model');
        $this->mergeConfigFrom(__DIR__.'/../config/menu.php', 'enso.structures.menu');
        $this->mergeConfigFrom(__DIR__.'/../config/permissions.php', 'enso.structures.permissions');
        $this->mergeConfigFrom(__DIR__.'/../config/package.php', 'enso.structures.package');
        $this->mergeConfigFrom(__DIR__.'/../config/params.php', 'enso.structures.params');
        $this->mergeConfigFrom(__DIR__.'/../config/files.php', 'enso.structures.files');
        $this->mergeConfigFrom(
            __DIR__.'/../config/permissionGroup.php',
            'enso.structures.permissionGroup'
        );

        return $this;
    }

    private function publish()
    {
        $this->publishes([
            __DIR__.'/../config' => config_path('enso/structures'),
        ], ['cli-config', 'enso-config']);

        return $this;
    }
}
