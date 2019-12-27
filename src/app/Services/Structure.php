<?php

namespace LaravelEnso\Cli\app\Services;

use Illuminate\Support\Str;
use LaravelEnso\Cli\app\Writers\FormWriter;
use LaravelEnso\Cli\app\Writers\ModelAndMigrationWriter;
use LaravelEnso\Cli\app\Writers\OptionsWriter;
use LaravelEnso\Cli\app\Writers\PackageWriter;
use LaravelEnso\Cli\app\Writers\RoutesWriter;
use LaravelEnso\Cli\app\Writers\StructureMigrationWriter;
use LaravelEnso\Cli\app\Writers\TableWriter;
use LaravelEnso\Cli\app\Writers\ValidatorWriter;
use LaravelEnso\Cli\app\Writers\ViewsWriter;

class Structure
{
    private $choices;
    private $isPackage;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;

        $this->preparePackage()
            ->prepareModel();
    }

    public function handle()
    {
        $this->writePackage()
            ->writeStructure()
            ->writeModelAndMigration()
            ->writeRoutes()
            ->writeViews()
            ->writeForm()
            ->writeTable()
            ->writeOptions();
    }

    public function writePackage()
    {
        if ($this->isPackage) {
            (new PackageWriter($this->choices))->handle();
        }

        return $this;
    }

    private function writeStructure()
    {
        (new StructureMigrationWriter($this->choices))->handle();

        return $this;
    }

    private function writeModelAndMigration()
    {
        if ($this->hasFile('model') || $this->hasFile('table migration')) {
            (new ModelAndMigrationWriter($this->choices))->handle();
        }

        return $this;
    }

    private function writeRoutes()
    {
        if ($this->hasFile('routes')) {
            (new RoutesWriter($this->choices))->handle();
        }

        return $this;
    }

    private function writeViews()
    {
        if ($this->hasFile('views')) {
            (new ViewsWriter($this->choices))->handle();
        }

        return $this;
    }

    private function writeForm()
    {
        if ($this->hasFile('form')) {
            (new FormWriter($this->choices))->handle();

            (new ValidatorWriter($this->choices))->handle();
        }

        return $this;
    }

    private function writeTable()
    {
        if ($this->hasFile('table')) {
            (new TableWriter($this->choices))->handle();
        }

        return $this;
    }

    private function writeOptions()
    {
        if ($this->hasFile('options')) {
            (new OptionsWriter($this->choices))->handle();
        }

        return $this;
    }

    private function preparePackage()
    {
        $this->isPackage = (bool) optional($this->choices->get('package'))->get('name');

        if ($this->isPackage) {
            $this->params()->set('root', $this->packageRoot());
            $this->params()->set('namespace', $this->packageNamespace());
        }

        return $this;
    }

    private function prepareModel()
    {
        if (! $this->choices->has('model')) {
            return $this;
        }

        $model = $this->choices->get('model');

        if (! Str::contains($model->get('name'), DIRECTORY_SEPARATOR)
            && ! $this->isPackage) {
            $model->set('namespace', 'App');

            return $this;
        }

        $segments = collect(explode(DIRECTORY_SEPARATOR, $model->get('name')));
        $model->set('name', $segments->pop());
        $model->set('namespace', $this->modelNamespace($segments));
        $model->set('path', $segments->implode(DIRECTORY_SEPARATOR));

        return $this;
    }

    private function packageNamespace()
    {
        return collect(explode(DIRECTORY_SEPARATOR, $this->packageRoot().'app'.DIRECTORY_SEPARATOR))
            ->reject(fn ($word) => collect(['src', 'vendor'])->contains($word))
            ->map(fn ($namespace, $word) => (
                $word === 'app' ? $word : ucfirst(Str::camel($word))
            ))->implode('\\');
    }

    private function packageRoot()
    {
        return 'vendor'.DIRECTORY_SEPARATOR
            .Str::kebab($this->choices->get('package')->get('vendor'))
            .DIRECTORY_SEPARATOR
            .Str::kebab($this->choices->get('package')->get('name'))
            .DIRECTORY_SEPARATOR.'src'.DIRECTORY_SEPARATOR;
    }

    private function modelNamespace($segments)
    {
        if ($this->isPackage) {
            return $segments->implode('\\')
                ? $this->packageNamespace().$segments->implode('\\')
                : collect(explode('\\', $this->packageNamespace()))
                    ->slice(0, -1)
                    ->implode('\\')
                    .$segments->implode('\\');
        }

        return 'App\\'.$segments->implode('\\');
    }

    private function hasFile($file)
    {
        return optional($this->choices->get('files'))->has($file);
    }

    private function params()
    {
        return $this->choices->params();
    }
}
