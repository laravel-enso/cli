<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\Cli\app\Services\Choices;

class RouteGenerator
{
    private const ShowRoute = 'show';

    private $choices;
    private $segments;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
        $this->isPackage = (bool) optional($this->choices->get('package'))->get('name');
    }

    public function handle()
    {
        [$from, $to] = $this->fromTo();

        $from[] = '${routes}';
        $to[] = $this->routes($from, $to);

        if (! $this->isPackage) {
            return str_replace($from, $to, $this->stub('routes'));
        }

        if (! File::isDirectory($this->packageRoutesPath())) {
            File::makeDirectory($this->packageRoutesPath(), 0755, true);
        }

        File::put(
            $this->packageRoutesPath().'api.php',
            str_replace($from, $to, $this->stub('routes'))
        );
    }

    private function fromTo()
    {
        $model = lcfirst($this->choices->get('model')->get('name'));

        $packagePrefix = $this->isPackage ? 'api/' : '';

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

    private function routes($from, $to)
    {
        $routes = collect(
            $this->choices->get('permissions')->all()
        )->filter()->keys();

        $showIndex = $routes->search(self::ShowRoute);

        if ($showIndex !== false) {
            tap($routes)->splice($showIndex, 1)->push(self::ShowRoute);
        }

        return $routes->reduce(fn($routes, $permission) => (
            $routes."\t\t".str_replace($from, $to, $this->stub($permission)).PHP_EOL
        ), PHP_EOL);
    }

    private function namespace()
    {
        $namespace = '';

        if ($this->isPackage) {
            $namespace .= $this->params()->get('namespace').'Http\Controllers\\';
        }

        $namespace .= $this->segments()
            ->map(fn($segment) => Str::ucfirst($segment))->implode('\\');

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
        return $this->segments ??= collect(
            explode('.', $this->choices->get('permissionGroup')->get('name'))
        );
    }

    private function packageRoutesPath()
    {
        return $this->params()->get('root')
            .'routes'
            .DIRECTORY_SEPARATOR;
    }

    private function params()
    {
        return $this->choices->params();
    }
}
