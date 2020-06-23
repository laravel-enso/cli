<?php

namespace LaravelEnso\Cli\Enums;

use LaravelEnso\Cli\Services\Validators\Menu;
use LaravelEnso\Cli\Services\Validators\Model;
use LaravelEnso\Enums\Services\Enum;

class Validators extends Enum
{
    public static array $data = [
        Options::Model => Model::class,
        Options::Menu => Menu::class,
    ];
}
