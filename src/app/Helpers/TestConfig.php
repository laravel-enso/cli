<?php

namespace LaravelEnso\Cli\app\Helpers;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class TestConfig
{
    public static function load()
    {
        return new Obj(json_decode(
            File::get(__DIR__.'/../Writers/stubs/test.stub')
        ));
    }
}
