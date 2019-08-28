<?php

namespace LaravelEnso\Cli\app\Services;

use Illuminate\Support\Str;
use LaravelEnso\Menus\app\Models\Menu;
use LaravelEnso\Helpers\app\Classes\Obj;

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
        $errors = collect();

        $model = $this->choices->get('model')->get('name');

        if (Str::contains($model, '\\')) {
            $errors->push('Namespaced models must only use slashes ("/")');
        }

        if (collect(explode('/', $model))->contains('')) {
            $errors->push('Namespaced models must only use one slash for each segment');
        }

        if ($errors->count()) {
            $this->errors['Model'] = $errors;
        }
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

            if ($this->choices->has('permissionGroup')
                && $this->choices->get('permissionGroup')
                    ->get('name') !== collect(explode('.', $menu->get('route')))
                        ->slice(0, -1)->implode('.')) {
                $errors->push('The menu\'s route does not match the configured permission group');
            }

            if (! collect($this->choices->get('permissions')->all())
                ->filter()->keys()
                ->contains(collect(explode('.', $menu->get('route')))->last())) {
                $errors->push('The menu\'s route does not match the configured permissions');
            }
        }

        if (! $menu->get('route') && ! $menu->get('has_children')) {
            $errors->push('A regular menu must have the route attribute filled');
        }

        if ($menu->filled('parentMenu')) {
            $matches = $this->parentMenuMatches($menu);

            if ($matches === 0) {
                $errors->push(
                    'The parent menu '
                        .$menu->get('parentMenu')
                        .' does not exist in the system'
                );
            }

            if ($matches > 1) {
                $errors->push(
                    'The parent menu '
                        .$menu->get('parentMenu')
                        .' is ambiguous. Please use dotted notation to specify its parent too.'
                );
            }
        }

        if ($errors->count()) {
            $this->errors['Menu'] = $errors;
        }
    }

    private function validateFiles()
    {
        //
    }

    private function validatePackage()
    {
        //
    }

    private function parentMenuMatches($menu)
    {
        $parentMenu = collect(explode('.', $menu->get('parentMenu')))
            ->pop();

        $parents = Menu::whereName($parentMenu)
            ->whereHasChildren(true)
            ->get();

        return $parents->reduce(function ($matches, $parent) use ($menu) {
            if ($parent->name === $menu->get('parentMenu')) {
                $matches++;

                return $matches;
            }

            $nestedMenu = $parent->name;

            while ($parent->parent_id !== null) {
                $parent = $parent->parent;
                $nestedMenu = $parent->name.'.'.$nestedMenu;

                if ($nestedMenu === $menu->get('parentMenu')) {
                    $matches++;
                    break;
                }
            }

            return $matches;
        }, 0);
    }
}
