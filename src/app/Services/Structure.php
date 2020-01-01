<?php

namespace LaravelEnso\Cli\App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Services\Writers\EnsoStructure;
use LaravelEnso\Cli\App\Services\Writers\Form;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Namespacer;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Migration;
use LaravelEnso\Cli\App\Services\Writers\Model;
use LaravelEnso\Cli\App\Services\Writers\Options;
use LaravelEnso\Cli\App\Services\Writers\Package;
use LaravelEnso\Cli\App\Services\Writers\Routes;
use LaravelEnso\Cli\App\Services\Writers\Table;
use LaravelEnso\Cli\App\Services\Writers\Views;
use LaravelEnso\Helpers\App\Classes\Obj;

class Structure
{
    private Choices $choices;
    private bool $isPackage;

    private array $providers = [
        'table' => Table::class,
        'form' => Form::class,
        'views' => Views::class,
        'routes' => Routes::class,
        'structure' => EnsoStructure::class,
        'options' => Options::class,
        'model' => Model::class,
    ];

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
    }

    public function handle()
    {
        $this->init()
            ->write();
    }

    private function init()
    {
        $this->initPackage()
            ->initModel()
            ->initSegments();

        return $this;
    }

    private function write()
    {
        $this->package()
            ->migration()
            ->writeProviders();
    }

    private function package()
    {
        if ($this->isPackage) {
            (new Package($this->choices))->handle();
        }

        return $this;
    }

    private function migration()
    {
        if ($this->hasFile('table')) {
            (new Migration($this->choices))->handle();
        }

        return $this;
    }

    private function writeProviders()
    {
        $this->choices->get('files', new Obj())
            ->filter()->keys()
            ->intersect(array_keys($this->providers))
            ->each(fn ($file) => $this->writeProvider($file));
    }

    private function writeProvider($file)
    {
        $provider = (new $this->providers[$file]($this->choices));

        WriterFactory::make($provider)->handle();
    }

    private function initPackage()
    {
        $this->isPackage = $this->choices->filled('package')
            && $this->choices->get('package')->filled('name');

        if ($this->isPackage) {
            $this->params()->set('root', $this->packageRoot());
            $this->params()->set('namespace', $this->packageNamespace('App'));
        }

        Path::root($this->params()->get('root'));
        Namespacer::prefix($this->params()->get('namespace'));

        return $this;
    }

    private function initModel()
    {
        if (! $this->choices->has('model')) {
            return $this;
        }

        $model = $this->choices->get('model');
        $segments = new Collection(explode(DIRECTORY_SEPARATOR, $model->get('name')));

        if ($segments->first() !== 'App') {
            $segments->prepend('App');
        }

        $model->set('name', Str::ucfirst($segments->pop()))
            ->set('namespace', $this->modelNamespace($segments))
            ->set('path', $this->modelPath($segments));

        return $this;
    }

    private function initSegments()
    {
        Segments::set($this->choices->get('permissionGroup'));

        return $this;
    }

    private function modelNamespace($segments)
    {
        $namespace = $segments->implode('\\');

        return $this->isPackage
            ? $this->packageNamespace($namespace)
            : $namespace;
    }

    private function modelPath($segments)
    {
        return $segments
            ->map(fn ($segment, $index) => ! $index ? lcfirst($segment) : $segment)
            ->implode(DIRECTORY_SEPARATOR);
    }

    private function packageNamespace(string $suffix)
    {
        return (new Collection(explode(DIRECTORY_SEPARATOR, $this->packageRoot())))
            ->reject(fn ($segment) => in_array($segment, ['src', 'vendor']))
            ->map(fn ($segment) => Str::ucfirst(Str::camel($segment)))
            ->push($suffix)
            ->implode('\\');
    }

    private function packageRoot()
    {
        $vendor = Str::kebab($this->choices->get('package')->get('vendor'));
        $package = Str::kebab($this->choices->get('package')->get('name'));

        return (new Collection(['vendor', $vendor, $package, 'src']))
            ->implode(DIRECTORY_SEPARATOR);
    }

    private function hasFile($file)
    {
        return $this->choices->filled('files')
            && $this->choices->get('files')->has($file);
    }

    private function params()
    {
        return $this->choices->params();
    }
}
