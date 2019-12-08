<?php

namespace LaravelEnso\Cli\app\Enums;

use LaravelEnso\Enums\app\Services\Enum;

class Options extends Enum
{
    public const Model = 'Model';
    public const PermissionGroup = 'Permission Group';
    public const Permissions = 'Permissions';
    public const Menu = 'Menu';
    public const Files = 'Files';
    public const Package = 'Package';
    public const Generate = 'Generate';
    public const ToggleValidation = 'Toggle Validation';
    public const Exit = 'Exit';

    public static function choices()
    {
        return collect([
            self::Model, self::PermissionGroup, self::Permissions,
            self::Menu, self::Files, self::Package,
        ]);
    }
}
