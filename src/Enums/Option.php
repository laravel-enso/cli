<?php

namespace LaravelEnso\Cli\Enums;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Services\Validators\Menu;
use LaravelEnso\Cli\Services\Validators\Model;
use LaravelEnso\Enums\Traits\Enum;

enum Option: string
{
    use Enum;

    case Model = 'Model';
    case PermissionGroup = 'Permission Group';
    case Permissions = 'Permissions';
    case Menu = 'Menu';
    case Files = 'Files';
    case Package = 'Package';
    case Generate = 'Generate';
    case ToggleValidation = 'Toggle Validation';
    case Exit = 'Exit';

    public static function choices(): Collection
    {
        return Collection::wrap([
            self::Model, self::PermissionGroup, self::Permissions,
            self::Menu, self::Files, self::Package,
        ])->map->value;
    }

    public function validator(): ?string
    {
        return match ($this) {
            self::Model => Model::class,
            self::Menu => Menu::class,
            default => null,
        };
    }
}
