<?php

namespace LaravelEnso\Cli\app\Helpers;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class TestConfig
{
    public static function loadStructure()
    {
        return new Obj(json_decode(
            File::get(__DIR__.'/../Writers/stubs/testStructure.stub')
        ));
    }

    public static function loadPackageStructure()
    {
        return new Obj(json_decode(
            File::get(__DIR__.'/../Writers/stubs/testPackageStructure.stub')
        ));
    }

    public static function loadParams()
    {
        return new Obj(json_decode(
            File::get(__DIR__.'/../Writers/stubs/testParams.stub')
        ));
    }
}
