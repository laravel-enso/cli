<?php

namespace LaravelEnso\StructureManager\app\Helpers;

use LaravelEnso\Helpers\app\Classes\Obj;

class TestConfig
{
    public static function load()
    {
        $choices = new Obj(json_decode(
            \File::get(__DIR__.'/../Writers/stubs/test.stub')
        ));

        collect($choices)->keys()
            ->each(function ($choice) use ($choices) {
                $choices->set($choice, new Obj($choices->get($choice)));
            });

        return $choices;
    }
}
