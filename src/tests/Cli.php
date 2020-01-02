<?php

namespace LaravelEnso\Cli\Tests;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\WriterFactory;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Namespacer;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Helpers\App\Classes\Obj;

trait Cli
{
    private $segments;

    private function initChoices()
    {
        $this->choices = (new Choices(new Command()))
            ->setChoices($this->choices())
            ->setParams($this->params());

        Path::root($this->params()->get('root'));
        Namespacer::prefix($this->params()->get('namespace'));
        Segments::set($this->choices->get('permissionGroup'));
    }

    private function assertControllerContains($needle, $controller)
    {
        $this->assertFileContains($needle, $this->controllerPath($controller));
    }

    private function assertControllerExists($controller)
    {
        $this->assertFileExists($this->controllerPath($controller));
    }

    private function assertValidatorContains($needle, $validator)
    {
        $this->assertFileContains($needle, $this->validatorPath($validator));
    }

    private function assertValidatorExists($validator)
    {
        $this->assertFileExists($this->validatorPath($validator));
    }

    private function assertFormBuilderContains($needle)
    {
        $this->assertFileContains($needle, $this->formBuilderPath());
    }

    private function assertFormBuilderExists()
    {
        $this->assertFileExists($this->formBuilderPath());
    }

    private function assertFormTemplateContains($needle)
    {
        $this->assertFileContains($needle, $this->formTemplatePath());
    }

    private function assertFormTemplateExists()
    {
        $this->assertFileExists($this->formTemplatePath());
    }

    private function assertCliFileContains($needle, $filePath)
    {
        $this->assertFileContains($needle, $this->path($filePath));
    }

    private function assertViewRouteContains($needle, $filePath)
    {
        $this->assertFileContains($needle, $this->viewRoutePath($filePath));
    }

    private function assertViewRouteExists($filePath)
    {
        $this->assertFileExists($this->viewRoutePath($filePath));
    }

    private function assertViewPageContains($needle, $method)
    {
        $this->assertFileContains($needle, $this->viewPagePath($method));
    }

    private function assertViewPageExists($method)
    {
        $this->assertFileExists($this->viewPagePath($method));
    }

    private function assertStructureMigrationContains($needle)
    {
        $this->assertFileContains($needle, $this->structureMigrationPath());
    }

    private function assertTableBuilderContains($needle)
    {
        $this->assertFileContains($needle, $this->tableBuilderPath());
    }

    private function assertTableBuilderExists()
    {
        $this->assertFileExists($this->tableBuilderPath());
    }

    private function assertTableTemplateContains($needle)
    {
        $this->assertFileContains($needle, $this->tableTemplatePath());
    }

    private function assertTableTemplateExists()
    {
        $this->assertFileExists($this->tableTemplatePath());
    }

    private function assertFileContains($needle, $filePath)
    {
        $content = File::get($filePath);

        (new Collection($needle))
            ->each(fn ($needle) => $this->assertStringContainsString($needle, $content));
    }

    private function assertMigrationCreated($migration)
    {
        $files = (new Collection(File::files($this->path(['database', 'migrations']))))
            ->filter(fn ($file) => Str::contains($file->getFilename(), $migration));

        $this->assertTrue($files->isNotEmpty(), $migration.' not exists!');
    }

    private function permissions()
    {
        return new Collection([
            'index' => 'index', 'create' => 'create', 'store' => 'store', 'edit' => 'edit',
            'exportExcel' => 'exportExcel', 'destroy' => 'destroy', 'initTable' => 'initTable',
            'tableData' => 'tableData', 'update' => 'update', 'options' => 'options', 'show' => 'show',
        ]);
    }

    private function setPermission($permissions)
    {
        $this->choices->put('permissions', $this->permissions()->only($permissions));
    }

    private function controllers()
    {
        return new Collection([
            'Index', 'Create', 'Edit', 'Update', 'Store', 'Show', 'Destroy', 'Options',
            'InitTable', 'TableData', 'ExportExcel',
        ]);
    }

    private function deleteMigration($migration)
    {
        $path = $this->path('database/migrations');

        if (! File::isDirectory($path)) {
            return;
        }

        $file = (new Collection(File::files($path)))
            ->filter(fn ($file) => Str::contains($file->getFilename(), $migration))
            ->last();

        File::delete($file);
    }

    private function controllerPath($controller): string
    {
        return $this->path([
            'app', 'Http', 'Controllers', ...$this->segments(), "{$controller}.php",
        ]);
    }

    private function validatorPath($validator): string
    {
        return $this->path([
            'app', 'Http', 'Requests', ...$this->segments(false), "{$validator}.php",
        ]);
    }

    private function formBuilderPath(): string
    {
        return $this->path([
            'app', 'Forms', 'Builders', ...$this->segments(false),
            Str::ucfirst("{$this->modelName()}Form.php")
        ]);
    }

    private function formTemplatePath(): string
    {
        return $this->path([
            'app', 'Forms', 'Templates', ...$this->segments(false),
            Str::camel($this->modelName()).'.json',
        ]);
    }

    private function viewRoutePath($filePath): string
    {
        return $this->path(['client', 'src', 'js', 'routes', ...(array) $filePath]);
    }

    private function viewPagePath($method): string
    {
        $segments = $this->segments(true)->map(fn ($segment) => lcfirst($segment));

        return $this->path([
            'client', 'src', 'js', 'pages', ...$segments, Str::ucfirst($method).'.vue',
        ]);
    }

    private function tableBuilderPath(): string
    {
        return $this->path([
            'app', 'Tables', 'Builders', ...$this->segments(false),
            Str::ucfirst($this->modelName()).'Table.php',
        ]);
    }

    private function tableTemplatePath(): string
    {
        return $this->path([
            'app', 'Tables', 'Templates', ...$this->segments(false),
            Str::camel(Str::plural($this->modelName())).'.json',
        ]);
    }

    private function structureMigrationPath()
    {
        $model = Str::snake(Str::plural($this->modelName()));
        $timestamp = Carbon::now()->format('Y_m_d_His');

        return $this->path([
            'database', 'migrations', "{$timestamp}_create_structure_for_{$model}.php",
        ]);
    }

    private function segments(bool $full = true)
    {
        Segments::set($this->choices->get('permissionGroup'));

        $this->segments ??= (new Collection(
            explode('.', $this->choices->get('permissionGroup')->get('name'))
        ))->map(fn ($segment) => Str::ucfirst($segment));

        return $full ? $this->segments : $this->segments->slice(0, -1);
    }

    private function modelName()
    {
        return (new Collection(
            explode('/', $this->choices->get('model')->get('name')))
        )->last();
    }

    private function write($provider)
    {
        WriterFactory::make(new $provider($this->choices))->handle();
    }

    private function tableName()
    {
        return Str::snake(Str::plural($this->modelName()));
    }

    private function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'group.testModels'],
            'model' => [
                'name' => 'TestModel',
                'namespace' => 'App',
            ],
            'permissions' => [],
            'files' => [],
        ]);
    }

    private function params()
    {
        return new Obj([
            'root' => $this->root,
            'namespace' => 'Namespace\App',
        ]);
    }

    private function path($segments)
    {
        return (new Collection([$this->root, ...(array) $segments]))
            ->filter()->implode(DIRECTORY_SEPARATOR);
    }
}
