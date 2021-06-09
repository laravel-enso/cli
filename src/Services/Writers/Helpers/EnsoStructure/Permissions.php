<?php

namespace LaravelEnso\Cli\Services\Writers\Helpers\EnsoStructure;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Services\Obj;

class Permissions
{
    public function __construct(
        private Obj $model,
        private ?Collection $permissions,
        private string $group
    ) {
    }

    public function get()
    {
        return $this->permissions
            ? $this->permissions->reduce(fn ($content, $permission) => $content
                .$this->permission($permission), '[').PHP_EOL
            .'    ]'
            : 'null';
    }

    private function permission(string $permission)
    {
        $fromTo = $this->fromTo();
        [$from, $to] = [array_keys($fromTo), array_values($fromTo)];
        $stub = Stub::get('permissions'.DIRECTORY_SEPARATOR.$permission);

        return PHP_EOL.str_replace($from, $to, $stub);
    }

    private function fromTo()
    {
        $model = Str::lower(str_replace('_', ' ', Str::snake($this->model->get('name'))));

        return [
            '${permissionGroup}' => $this->group,
            '${model}' => $model,
            '${models}' => Str::plural($model),
        ];
    }
}
