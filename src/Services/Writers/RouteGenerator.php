<?php

namespace LaravelEnso\Cli\Services\Writers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Services\Obj;

class RouteGenerator
{
    private const ShowRoute = 'show';

    private Obj $params;
    private Obj $model;
    private string $group;
    private Collection $permissions;
    private bool $isPackage;

    public function __construct(Choices $choices)
    {
        $this->params = $choices->params();
        $this->model = $choices->get('model');
        $this->group = $choices->get('permissionGroup')->get('name');
        $this->permissions = $choices->get('permissions')->filter()->keys();
        $this->isPackage = $choices->filled('package')
            && $choices->get('package')->filled('name');
    }

    public function handle()
    {
        Segments::ucfirst(false);
        Stub::folder('api');
        $this->sortPermissions();

        [$from, $to] = $this->fromTo();

        $from[] = '${routes}';
        $to[] = $this->routes($from, $to);

        $from[] = '${uses}';
        $to[] = $this->uses($from, $to);

        $content = str_replace($from, $to, Stub::get('routes'));

        if (! $this->isPackage) {
            File::put($this->appRoutesPath(), $content);

            return $this->appRoutesPath(null);
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
            '${prefix}' => "prefix('{$packagePrefix}{$prefix}')",
            '${alias}' => "as('{$alias}.')",
            '${model}' => lcfirst($this->model->get('name')),
        ];

        return [array_keys($array), array_values($array)];
    }

    private function routes($from, $to)
    {
        return $this->permissions
            ->map(fn ($permission) => $this->route($from, $to, $permission))
            ->map(fn ($route) => trim($route))
            ->implode(PHP_EOL."\t\t");
    }

    private function uses($from, $to)
    {
        return $this->permissions
            ->map(fn ($permission) => $this->use($from, $to, $permission))
            ->push('use Illuminate\Support\Facades\Route;')
            ->map(fn ($use) => trim($use))
            ->sort()
            ->implode(PHP_EOL);
    }

    private function route($from, $to, $permission)
    {
        return str_replace($from, $to, Stub::get($permission));
    }

    private function use($from, $to, $permission)
    {
        return str_replace(
            ['${namespace}', '${permission}'],
            [$this->namespacer(), Str::ucfirst($permission)],
            Stub::get('use')
        );
    }

    private function namespacer()
    {
        $segments = new Collection();
        $segments = $segments->concat([$this->params->get('namespace'), 'Http', 'Controllers']);

        return $segments->concat(Segments::get())
            ->map(fn ($segment) => Str::ucfirst($segment))
            ->implode('\\');
    }

    private function packageRoutesPath(?string $file = null)
    {
        return Collection::wrap([$this->params->get('root'), 'routes', $file])
            ->implode(DIRECTORY_SEPARATOR);
    }

    private function appRoutesPath(?string $directory = 'routes')
    {
        Path::segments(false);
        $segments = explode('.', $this->group);
        $path = Path::get([$directory, 'app', ...array_slice($segments, 0, -1)], null, true);
        Directory::prepare($path);

        return $path.DIRECTORY_SEPARATOR.last($segments).'.php';
    }

    private function sortPermissions(): void
    {
        $showIndex = $this->permissions->search(self::ShowRoute);

        if ($showIndex !== false) {
            tap($this->permissions)->splice($showIndex, 1)->push(self::ShowRoute);
        }
    }
}
