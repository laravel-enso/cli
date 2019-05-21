<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use LaravelEnso\Helpers\app\Classes\Obj;

class FormWriter
{
    private const CrudOperations = ['index', 'create', 'store', 'show', 'edit', 'update', 'destroy'];

    private $choices;
    private $segments;

    public function __construct(Obj $choices)
    {
        $this->choices = $choices;
    }

    public function run()
    {
        $this->createFolders()
            ->writeTemplate()
            ->writeBuilder()
            ->writeRequest()
            ->writeControllers();
    }

    private function createFolders()
    {
        if (! File::isDirectory($this->builderPath())) {
            File::makeDirectory($this->builderPath(), 0755, true);
        }

        if (! File::isDirectory($this->templatePath())) {
            File::makeDirectory($this->templatePath(), 0755, true);
        }

        if (! File::isDirectory($this->controllerPath())) {
            File::makeDirectory($this->controllerPath(), 0755, true);
        }

        return $this;
    }

    private function writeTemplate()
    {
        File::put(
            $this->templateName(),
            str_replace(
                '${permissionGroup}',
                $this->choices->get('permissionGroup')->get('name'),
                $this->stub('template')
            )
        );

        return $this;
    }

    private function templateName()
    {
        return $this->templatePath()
            .DIRECTORY_SEPARATOR
            .Str::camel($this->choices->get('model')->get('name'))
            .'.json';
    }

    private function writeBuilder()
    {
        [$from, $to] = $this->builderFromTo();

        File::put(
            $this->builderName(),
            str_replace($from, $to, $this->stub('builder'))
        );

        return $this;
    }

    private function builderFromTo()
    {
        $model = $this->choices->get('model');

        $array = [
            '${relativePath}' => $this->segments()->count() > 1
                ? $this->segments()->slice(0, -1)->implode('/').'/'
                : '',
            '${namespace}' => 'App\\Forms\\Builders'
                .($this->segments()->count() > 1
                    ? '\\'.$this->segments()->slice(0, -1)->implode('\\')
                    : ''),
            '${modelNamespace}' => $model->get('namespace'),
            '${depth}' => str_repeat('../', $this->segments()->count()),
            '${model}' => Str::camel($model->get('name')),
            '${Model}' => $model->get('name'),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function builderName()
    {
        return $this->builderPath()
            .DIRECTORY_SEPARATOR
            .$this->choices->get('model')->get('name')
            .'Form.php';
    }

    private function writeRequest()
    {
        Artisan::call('make:request', [
            'name' => $this->request(),
        ]);

        return $this;
    }

    private function request()
    {
        return $this->segments()->slice(0, -1)->implode('\\')
            .'\\'.'Validate'
            .$this->choices->get('model')->get('name')
            .'Request';
    }

    private function writeControllers()
    {
        [$from, $to] = $this->controllerFromTo();

        $this->choices->get('permissions')
            ->filter()
            ->keys()
            ->intersect(self::CrudOperations)
            ->each(function ($permission) use ($from, $to) {
                File::put(
                    $this->controllerName($permission),
                    str_replace($from, $to, $this->stub($permission))
                );
            });

        return $this;
    }

    private function controllerFromTo()
    {
        $model = $this->choices->get('model');

        $namespaceSuffix = $this->segments()->count() > 1
            ? '\\'.$this->segments()->slice(0, -1)->implode('\\')
            : '';

        $array = [
            '${Model}' => $model->get('name'),
            '${model}' => Str::lower($model->get('name')),
            '${permissionGroup}' => $this->choices->get('permissionGroup')->get('name'),
            '${namespace}' => 'App\\Http\\Controllers\\'.$this->segments()->implode('\\'),
            '${modelNamespace}' => $model->get('namespace'),
            '${builderNamespace}' => 'App\\Forms\\Builders'.$namespaceSuffix,
            '${requestNamespace}' => 'App\\Http\\Requests'.$namespaceSuffix,
            '${request}' => 'Validate'.$model->get('name').'Request',
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function controllerName($permission)
    {
        return $this->controllerPath()
            .DIRECTORY_SEPARATOR
            .Str::ucfirst($permission).
            '.php';
    }

    private function builderPath()
    {
        return app_path(
            'Forms'.DIRECTORY_SEPARATOR
            .'Builders'.DIRECTORY_SEPARATOR
            .$this->segments()->slice(0, -1)
                ->implode(DIRECTORY_SEPARATOR)
        );
    }

    private function templatePath()
    {
        return app_path(
            'Forms'.DIRECTORY_SEPARATOR
            .'Templates'.DIRECTORY_SEPARATOR
            .$this->segments()->slice(0, -1)
                ->implode(DIRECTORY_SEPARATOR)
        );
    }

    private function controllerPath()
    {
        return app_path(
            'Http'.DIRECTORY_SEPARATOR.
            'Controllers'.DIRECTORY_SEPARATOR
            .$this->segments()->implode(DIRECTORY_SEPARATOR)
        );
    }

    private function stub($file)
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR
            .'stubs'.DIRECTORY_SEPARATOR
            .'form'.DIRECTORY_SEPARATOR
            .$file.'.stub'
        );
    }

    private function segments()
    {
        return $this->segments
            ?? $this->segments = collect(
                explode('.', $this->choices->get('permissionGroup')->get('name'))
            )->map(function ($segment) {
                return Str::ucfirst($segment);
            });
    }
}
