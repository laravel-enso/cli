<?php

namespace LaravelEnso\Cli\app\Enums;

use LaravelEnso\Enums\app\Classes\Enum;

class Menus extends Enum
{
    const Main = 'Model';
    const Depended = 'Permission Group';
    const Permissions = 'Permissions';
    const Menu = 'Menu';
    const Files = 'Files';
    const Package = 'Package';
    const Generate = 'Generate';
    const ToggleValidation = 'Toggle Validation';
    const Close = 'Close';

    public static function choices()
    {
        return collect([
            self::Main, self::Depended, self::Permissions,
            self::Menu, self::Files, self::Package,
        ]);
    }
}
