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
    private $params;
    private $segments;

    public function __construct(Obj $choices, Obj $params)
    {
        $this->choices = $choices;
        $this->params = $params;
        $this->isPackage = (bool) optional($this->choices->get('package'))->get('name');
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

        if ($this->isPackage) {
            if (! File::isDirectory($this->packageRoutesPath())) {
                File::makeDirectory($this->packageRoutesPath(), 0755, true);
            }

            File::put(
                $this->packageRoutesPath().'api.php',
                str_replace($from, $to, $this->stub('routes'))
            );

            return;
        }

        return str_replace($from, $to, $this->stub('routes'));
    }

    private function fromTo()
    {
        $model = lcfirst($this->choices->get('model')->get('name'));
        $packagePrefix = $this->isPackage
            ? 'api/'
            : '';

        $groupPrefix = "->prefix('"
            .$packagePrefix
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
        $namespace = '';
        if ($this->isPackage) {
            $namespace .= $this->params->get('namespace').'Http\Controllers\\';
        }
        $namespace .= $this->segments()
            ->map(function ($segment) {
                return Str::ucfirst($segment);
            })->implode('\\');

        return $namespace;
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

    private function packageRoutesPath()
    {
        return $this->params->get('root')
            .'routes'
            .DIRECTORY_SEPARATOR;
    }
}
