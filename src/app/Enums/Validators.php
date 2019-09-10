<?php

namespace LaravelEnso\Cli\app\Enums;

use LaravelEnso\Enums\app\Classes\Enum;
use LaravelEnso\Cli\app\Services\Validators\Menu;
use LaravelEnso\Cli\app\Services\Validators\Model;

class Validators extends Enum
{
    public static function attributes()
    {
        return [
            Options::Model => Model::class,
            Options::Menu => Menu::class,
        ];
    }
}
