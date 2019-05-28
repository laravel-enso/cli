<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class RouteGenerator
{
    private const RouteOrder = [
        'index', 'create', 'store', 'edit', 'update', 'destroy', 'initTable',
        'tableData', 'exportExcel', 'options', 'show',
    ];

    private $choices;
    private $segments;

    public function __construct(Obj $choices)
    {
        $this->choices = $choices;
    }

    public function run()
    {
        [$from, $to] = $this->fromTo();

        $routes = collect(self::RouteOrder)
            ->intersect($this->routes())
            ->reduce(function ($routes, $permission) use ($from, $to) {
                return $routes."\t\t"
                    .str_replace($from, $to, $this->stub($permission))
                    .PHP_EOL;
            }, PHP_EOL);

        $from[] = '${routes}';
        $to[] = $routes;

        return str_replace($from, $to, $this->stub('routes'));
    }

    private function fromTo()
    {
        $model = Str::lower($this->choices->get('model')->get('name'));

        $groupPrefix = "->prefix('"
            .$this->segments()->implode('/')
            ."')->as('"
            .$this->segments()->implode('.')
            .".')";

        $array = [
            '${namespace}' => $this->namespace(),
            '${groupPrefix}' => $groupPrefix,
            '${model}' => $model,
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function routes()
    {
        return collect(
            $this->choices->get('permissions')->all()
        )->filter()
        ->keys();
    }

    private function namespace()
    {
        return $this->segments()
            ->map(function ($segment) {
                return Str::ucfirst($segment);
            })->implode('\\');
    }

    private function stub($file)
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'api'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function segments()
    {
        return $this->segments
            ?? $this->segments = collect(
                explode('.', $this->choices->get('permissionGroup')->get('name'))
            );
    }
}
