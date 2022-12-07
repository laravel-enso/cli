<?php

namespace LaravelEnso\Cli\Services\Writers\Helpers\EnsoStructure;

use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Services\Obj;

class Mapping
{
    public function __construct(
        private ?Obj $menu,
        private ?string $group
    ) {
    }

    public function menu()
    {
        return $this->menu
            ? str_replace($this->mapping(), $this->values(), Stub::get('menu'))
            : '[]';
    }

    public function parentMenu()
    {
        return $this->menu
            ? str_replace(
                '${parentMenu}',
                "'{$this->menu->get('parentMenu')}'",
                Stub::get('parentMenu')
            ) : 'null';
    }

    private function mapping()
    {
        return $this->menu->keys()
            ->map(fn ($key) => '$'."{{$key}}")->toArray();
    }

    private function values()
    {
        if (!$this->menu->get('has_children')) {
            $this->menu->set('route', "{$this->group}.{$this->menu->get('route')}");
        }

        return $this->menu->values()
            ->map(fn ($value) => $this->writableValue($value))
            ->toArray();
    }

    private function writableValue($value)
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return is_string($value) && $value === '' ? 'null' : $value;
    }
}
