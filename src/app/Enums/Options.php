<?php

namespace LaravelEnso\Cli\app\Enums;

use LaravelEnso\Enums\app\Classes\Enum;

class Options extends Enum
{
    const Model = 'Model';
    const PermissionGroup = 'Permission Group';
    const Permissions = 'Permissions';
    const Menu = 'Menu';
    const Files = 'Files';
    const Package = 'Package';
    const Generate = 'Generate';
    const ToggleValidation = 'Toggle Validation';
    const Exit = 'Exit';

    public static function choices()
    {
        return collect([
            self::Model, self::PermissionGroup, self::Permissions,
            self::Menu, self::Files, self::Package,
        ]);
    }
}
