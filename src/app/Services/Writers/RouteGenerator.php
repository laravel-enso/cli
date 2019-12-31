<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class RouteGenerator
{
    private const ShowRoute = 'show';

    private Obj $params;
    private Obj $model;
    private Collection $permissions;
    private bool $isPackage;

    public function __construct(Choices $choices)
    {
        $this->params = $choices->params();
        $this->model = $choices->get('model');
        $this->permissions = $choices->get('permissions')->filter()->keys();
        $this->isPackage = $choices->filled('package')
            && $choices->get('package')->filled('name');
    }

    public function handle()
    {
        Segments::ucfirst(false);
        Stub::folder('api');

        [$from, $to] = $this->fromTo();

        $from[] = '${routes}';
        $to[] = $this->routes($from, $to);
        $content = str_replace($from, $to, Stub::get('routes'));

        if (! $this->isPackage) {
            return $content;
        }

        Directory::prepare($this->packageRoutesPath());

        File::put($this->packageRoutesPath('api.php'), $content);
    }

    private function fromTo()
    {
        $packagePrefix = $this->isPackage ? 'api/' : '';
        $prefix = Segments::get()->implode('/');
        $alias = Segments::get()->implode('.');

        $array = [
            '${namespace}' => $this->namespacer(),
            '${prefix}' => "->prefix('{$packagePrefix}{$prefix}')",
            '${alias}' => "->as('{$alias}.')",
            '${model}' => lcfirst($this->model->get('name')),
        ];

        return [array_keys($array), array_values($array)];
    }

    private function routes($from, $to)
    {
        $showIndex = $this->permissions->search(self::ShowRoute);

        if ($showIndex !== false) {
            tap($this->permissions)->splice($showIndex, 1)->push(self::ShowRoute);
        }

        return $this->permissions
            ->reduce(fn ($routes, $permission) => $routes
                .$this->route($from, $to, $permission), PHP_EOL);
    }

    private function route($from, $to, $permission)
    {
        return "\t\t".str_replace($from, $to, Stub::get($permission)).PHP_EOL;
    }

    private function namespacer()
    {
        $segments = new Collection();

        if ($this->isPackage) {
            $segments = $segments->concat([$this->params->get('namespace'), 'Http', 'Controllers']);
        }

        return $segments->concat(Segments::get())
            ->map(fn ($segment) => Str::ucfirst($segment))
            ->implode('\\');
    }

    private function packageRoutesPath(?string $file = null)
    {
        return (new Collection([$this->params->get('root'), 'routes',  $file]))
            ->implode(DIRECTORY_SEPARATOR);
    }
}
