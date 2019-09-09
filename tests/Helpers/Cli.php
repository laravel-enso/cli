<?php

namespace LaravelEnso\Cli\tests\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\app\Services\Choices;

trait Cli
{
    private function initChoices()
    {
        $this->choices = (new Choices(new Command))
            ->setChoices($this->choices())
            ->setParams($this->params());
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
        $this->assertFileContains($needle, $this->root.$filePath);
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

        collect($needle)->each(function ($needle) use ($content) {
            $this->assertContains($needle, $content);
        });
    }

    private function assertMigrationCreated($migration)
    {
        $files = collect(File::files($this->root.'database/migrations'))
            ->filter(function ($file) use ($migration) {
                return Str::contains($file->getFilename(), $migration);
            });

        $this->assertTrue($files->isNotEmpty(), $migration. ' not exists!');
    }

    private function permissions()
    {
        return collect([
            'index' => 'index', 'create' => 'create', 'store' => 'store', 'edit' => 'edit',
            'exportExcel' => 'exportExcel', 'destroy' => 'destroy', 'initTable' => 'initTable',
            'tableData' => 'tableData', 'update' => 'update', 'options' => 'options', 'show' => 'show',
        ]);
    }

    protected function setPermission($permissions)
    {
        $this->choices->put('permissions', $this->permissions()->only($permissions));
    }

    private function controllers()
    {
        return collect([
            'Index', 'Create', 'Edit', 'Update', 'Store', 'Show', 'Destroy', 'Options',
            'InitTable', 'TableData', 'ExportExcel',
        ]);
    }

    private function deleteMigration($migration)
    {
        if (! File::isDirectory($this->root.'database/migrations')) {
            return;
        }

        $file = collect(File::files($this->root.'database/migrations'))
            ->filter(function ($file) use ($migration) {
                return Str::contains($file->getFilename(), $migration);
            })->last();

        File::delete($file);
    }

    private function controllerPath($controller): string
    {
        return $this->root.'app/Http/Controllers/'
            .$this->permissionGroupPath()
            .$controller.'.php';
    }

    private function validatorPath($validator): string
    {
        return $this->root.'app/Http/Requests/'
            .$this->permissionGroupPath()
            .$validator.'.php';
    }

    private function formBuilderPath(): string
    {
        return $this->root.'app/Forms/Builders/'
            .$this->permissionGroupPath(false)
            .ucfirst($this->modelName()).'Form.php';
    }

    private function formTemplatePath(): string
    {
        return $this->root.'app/Forms/Templates/'
            .$this->permissionGroupPath(false)
            .Str::camel($this->modelName()).'.json';
    }

    private function viewRoutePath($filePath): string
    {
        return $this->root.'resources/js/routes/'.$filePath;
    }

    private function viewPagePath($method): string
    {
        return $this->root.'resources/js/pages/'.
            $this->segments()->implode('/').'/'
            .ucfirst($method).'.vue';
    }

    private function tableBuilderPath(): string
    {
        return $this->root.'app/Tables/Builders/'
            .$this->permissionGroupPath(false)
            .ucfirst($this->modelName()).'Table.php';
    }

    private function tableTemplatePath(): string
    {
        return $this->root.'app/Tables/Templates/'
            .$this->permissionGroupPath(false)
            .Str::camel(Str::plural($this->modelName())).'.json';
    }

    private function structureMigrationPath()
    {
        $model = Str::snake(Str::plural($this->modelName()));
        $timestamp = Carbon::now()->format('Y_m_d_His');

        return $this->root.'database/migrations/'.
            $timestamp.'_create_structure_for_'.$model.'.php';
    }

    private function segments()
    {
        return collect(explode('.', $this->choices->get('permissionGroup')->get('name')));
    }

    private function ucfirstSegments()
    {
        return $this->segments()->map(function ($perm) {
            return ucfirst($perm);
        });
    }

    private function modelName()
    {
        return collect(explode('/', $this->choices->get('model')->get('name')))->last();
    }

    private function permissionGroupPath(bool $isFull = true)
    {
        $segments = $isFull
            ? $this->ucfirstSegments()
            : $this->ucfirstSegments()->slice(0, -1);

        return $segments->implode('/').'/';
    }
}
