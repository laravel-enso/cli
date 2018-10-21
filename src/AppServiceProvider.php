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
    }

    public function register()
    {
        //
    }
}
