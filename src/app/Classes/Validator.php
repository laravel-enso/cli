<?php

namespace LaravelEnso\StructureManager\app\Classes;

use Illuminate\Support\Str;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\MenuManager\app\Models\Menu;

class Validator
{
    private $choices;
    private $configured;
    private $errors;

    public function __construct(Obj $choices, $configured)
    {
        $this->choices = $choices;
        $this->configured = $configured;
        $this->errors = collect();
    }

    public function run()
    {
        $this->configured->each(function ($choice) {
            $this->{'validate'.Str::ucfirst(Str::camel($choice))}();
        });

        return $this;
    }

    public function fails()
    {
        return $this->errors
            ->isNotEmpty();
    }

    public function errors()
    {
        return $this->errors;
    }

    private function validateModel()
    {
        $this->choices->get('model')->set(
            'name',
            Str::ucfirst($this->choices->get('model')->get('name'))
        );
    }

    private function validatePermissionGroup()
    {
        //
    }

    private function validatePermissions()
    {
        //
    }

    private function validateMenu()
    {
        if (! $this->choices->has('menu')) {
            return;
        }

        $errors = collect();
        $menu = $this->choices->get('menu');

        if ($menu->get('route')) {
            if ($menu->get('has_children')) {
                $errors->push('A parent menu must have the route attribute empty');
            }

            if ($this->choices->has('permissionGroup') &&
                $this->choices->get('permissionGroup')->get('name')
                !== collect(explode('.', $menu->get('route')))->slice(0, -1)->implode('.')) {
                $errors->push('The menu\'s route does not match the configured permission group');
            }
        }

        if (! $menu->get('route') && ! $menu->get('has_children')) {
            $errors->push('A regular menu must have the route attribute filled');
        }

        if ($menu->filled('parentMenu')
            && Menu::whereName($menu->get('parentMenu'))->first() === null) {
            $errors->push(
                'The parent menu '
                .$menu->get('parentMenu')
                .' does not exist in the system'
            );
        }

        if ($errors->count()) {
            $this->errors['Menu'] = $errors;
        }
    }

    private function validateFiles()
    {
        //
    }
}
