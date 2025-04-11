<?php

namespace LaravelEnso\Cli\Enums;

use Illuminate\Support\Collection;

enum Option: string
{
    case Model = 'Model';
    case PermissionGroup = 'Permission Group';
    case Permissions = 'Permissions';
    case Menu = 'Menu';
    case Files = 'Files';
    case Package = 'Package';
    case Generate = 'Generate';
    case ToggleValidation = 'Toggle Validation';
    case Exit = 'Exit';

    public static function choices()
    {
        return new Collection([
            self::Model->value, self::PermissionGroup->value, self::Permissions->value,
            self::Menu->value, self::Files->value, self::Package->value,
        ]);
    }
}
