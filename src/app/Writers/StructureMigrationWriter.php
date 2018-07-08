<?php

namespace LaravelEnso\StructureManager\app\Writers;

use LaravelEnso\Helpers\app\Classes\Obj;

class StructureMigrationWriter
{
    private $choices;
    private $name;

    public function __construct(Obj $choices)
    {
        $this->choices = $choices;
    }

    public function run()
    {
        [$from, $to] = $this->replaceFromTo();

        \File::put(
            database_path('migrations/'.$this->name()),
            str_replace($from, $to, $this->stub('migration'))
        );
    }

    private function replaceFromTo()
    {
        $array = [
            '${Entity}' => str_plural($this->entity()),
            '${menu}' => $this->menu(),
            '${parentMenu}' => $this->parentMenu(),
            '${permissionGroup}' => $this->permissionGroup(),
            '${permissions}' => $this->permissions(),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function menu()
    {
        if ($this->choices->has('menu')) {
            $menu = $this->choices->get('menu');

            $stub = str_replace(
                $this->mapping($menu->keys()),
                $this->writableValues($menu->values()),
                $this->stub('menu')
            );
        }

        return $stub ?? 'null';
    }

    private function parentMenu()
    {
        if ($this->choices->has('menu')) {
            $stub = str_replace(
                '${parentMenu}',
                $this->choices->get('menu')->get('parentMenu'),
                $this->stub('parentMenu')
            );
        }

        return isset($stub) && $stub ? $stub : 'null';
    }

    private function permissionGroup()
    {
        if ($this->choices->has('permissionGroup')) {
            $group = $this->choices->get('permissionGroup');

            $group->set(
                'description',
                str_plural($this->model()).' Permission Group'
            );

            $stub = str_replace(
                $this->mapping($group->keys()),
                $group->values(),
                $this->stub('permissionGroup')
            );
        }

        return $stub ?? 'null';
    }

    private function permissions()
    {
        if ($this->choices->has('permissions')) {
            $stub = collect($this->choices->get('permissions'))
                ->filter()
                ->keys()
                ->reduce(function ($content, $permission) {
                    [$from, $to] = $this->permissionReplaceFromTo();
                    $stub = $this->stub('permissions'.DIRECTORY_SEPARATOR.$permission);

                    return $content.PHP_EOL.str_replace($from, $to, $stub);
                }, '');
        }

        return isset($stub)
            ? '['.$stub.PHP_EOL.'    ]'
            : 'null';
    }

    private function permissionReplaceFromTo()
    {
        $array = [
            '${permissionGroup}' => $this->choices->get('permissionGroup')->get('name'),
            '${model}' => strtolower($this->model()),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function model()
    {
        return $this->choices->has('model')
            ? $this->choices->get('model')->get('name')
            : null;
    }

    private function entity()
    {
        return $this->choices->has('model')
            ? $this->choices->get('model')->get('name')
            : $this->choices->get('menu')->get('name');
    }

    private function mapping($keys)
    {
        return collect($keys)->map(function ($key) {
            return '${'.$key.'}';
        })->toArray();
    }

    private function writableValues($values)
    {
        return collect($values)->map(function ($value) {
            if (is_bool($value)) {
                return $value
                    ? 'true'
                    : 'false';
            }

            return $value;
        })->toArray();
    }

    private function name()
    {
        return now()->format('Y_m_d_His')
            .'_create_structure_for_'
            .snake_case(str_plural($this->entity()))
            .'.php';
    }

    private function stub($stub)
    {
        return \File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'structure'
            .DIRECTORY_SEPARATOR.$stub.'.stub'
        );
    }
}
