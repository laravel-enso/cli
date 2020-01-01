<?php

namespace LaravelEnso\Cli\App\Enums;

use LaravelEnso\Cli\App\Services\Validators\Menu;
use LaravelEnso\Cli\App\Services\Validators\Model;
use LaravelEnso\Enums\App\Services\Enum;

class Validators extends Enum
{
    public static array $data = [
        Options::Model => Model::class,
        Options::Menu => Menu::class,
    ];
}
