<?php

namespace LaravelEnso\StructureManager\app\Classes\Helpers;

use LaravelEnso\Helpers\app\Classes\Obj;

class Writer
{
    private $structure;
    private $migrationName;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;
        $this->migrationName = $this->migrationName();
    }

    public function run()
    {
        $this->writeMigration();
    }

    private function writeMigration()
    {
        $replaceArray = array_filter($this->replaceArray());

        $migration = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $this->stub('structureMigration')
        );

        $path = database_path('migrations/'.$this->migrationName);

        \File::put($path, $migration);
    }

    private function replaceArray()
    {
        return [
            'CreateStructureForClass' => $this->class(),
            '$menu'                   => $this->menu(),
            '$parentMenu'             => $this->parentMenu(),
            '$permissionGroup'        => $this->permissionGroup(),
            '$permissions'            => $this->permissions(),
        ];
    }

    private function class()
    {
        return 'CreateStructureFor'.str_plural($this->name());
    }

    private function menu()
    {
        if ($this->structure->has('menu')) {
            $menu = $this->structure->get('menu');

            $stub = str_replace(
                $this->keyMappings($menu->keys()),
                $this->writableValues($menu->values()),
                $this->stub('menu')
            );
        }

        return isset($stub)
            ? '$menu = '.$stub
            : null;
    }

    private function parentMenu()
    {
        if ($this->structure->has('menu')) {
            $stub = str_replace(
                '${parentMenu}',
                $this->structure->get('menu')->parentMenu,
                $this->stub('parentMenu')
            );
        }

        return isset($stub)
            ? '$parentMenu = '.$stub
            : null;
    }

    private function permissionGroup()
    {
        if ($this->structure->has('permissionGroup')) {
            $group = $this->structure->get('permissionGroup');

            $group->set(
                'description',
                str_plural($this->model()).' Permission Group'
            );

            $stub = str_replace(
                $this->keyMappings($group->keys()),
                $group->values(),
                $this->stub('permissionGroup')
            );
        }

        return isset($stub)
            ? '$permissionGroup = '.$stub
            : null;
    }

    private function permissions()
    {
        if ($this->structure->has('permissions')) {
            $permissions = $this->structure->get('permissions');

            $stub = collect($permissions->keys())
                ->filter(function ($permission) use ($permissions) {
                    return $permissions->$permission;
                })->reduce(function ($stub, $permission) {
                    return $stub.PHP_EOL
                    .str_replace(
                        ['${model}', '${prefix}'],
                        [
                            strtolower($this->model()),
                            $this->structure->get('permissionGroup')->get('name'),
                        ],
                        $this->stub('permissions/'.$permission)
                    );
                }, '');
        }

        return isset($stub)
            ? '$permissions = ['.$stub.PHP_EOL.'    ]'
            : null;
    }

    private function model()
    {
        return $this->structure->has('model')
            ? $this->structure->get('model')->get('name')
            : null;
    }

    private function migrationName()
    {
        return now()->format('Y_m_d_His')
            .'_create_structure_for_'
            .snake_case($this->name()).'.php';
    }

    private function name()
    {
        $entity = $this->structure->has('model')
            ? $this->structure->get('model')
            : $this->structure->get('menu');

        return str_plural($entity->name);
    }

    private function keyMappings($keys)
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

    private function stub($stub)
    {
        return \File::get(__DIR__.'/../stubs/'.$stub.'.stub');
    }
}
