<?php

namespace LaravelEnso\Cli\app\Writers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class TableWriter
{
    private const TableOperations = ['initTable', 'tableData', 'exportExcel'];

    private $choices;
    private $segments;
    private $params;

    public function __construct(Obj $choices, Obj $params)
    {
        $this->choices = $choices;
        $this->params = $params;
    }

    public function run()
    {
        $this->createFolders()
            ->writeTemplate()
            ->writeBuilder()
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
        [$from, $to] = $this->templateFromTo();

        File::put(
            $this->templateName(),
            str_replace($from, $to, $this->stub('template'))
        );

        return $this;
    }

    private function templateFromTo()
    {
        $model = $this->choices->get('model')->get('name');

        $array = [
            '${permissionGroup}' => $this->choices->get('permissionGroup')->get('name'),
            '${Models}' => Str::title(
                collect(explode('_', Str::snake($model)))->implode(' ')
                ),
            '${models}' => Str::snake(Str::plural($model)),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function templateName()
    {
        return $this->templatePath()
            .DIRECTORY_SEPARATOR
            .Str::camel(
                Str::plural(
                    $this->choices->get('model')->get('name')
            )
            ).'.json';
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
            '${namespace}' => $this->params->get('namespace')
                .'Tables\\Builders'
                .($this->segments()->count() > 1
                    ? '\\'.$this->segments()->slice(0, -1)->implode('\\')
                    : ''),
            '${modelNamespace}' => $model->get('namespace'),
            '${Model}' => $model->get('name'),
            '${models}' => Str::camel(Str::plural($model->get('name'))),
            '${table}' => Str::snake(Str::plural($model->get('name'))),
            '${depth}' => str_repeat('../', $this->segments()->count() - 1),
            '${relativePath}' => $this->segments()->count() > 1
                ? $this->segments()->slice(0, -1)->implode('/').'/'
                : '',
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
            .'Table.php';
    }

    private function writeControllers()
    {
        [$from, $to] = $this->controllerFromTo();

        $this->choices->get('permissions')
            ->filter()
            ->keys()
            ->intersect(self::TableOperations)
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
        $array = [
            '${namespace}' => $this->params->get('namespace')
                .'Http\\Controllers\\'
                .$this->segments()->implode('\\'),
            '${builderNamespace}' => $this->params->get('namespace').'Tables\\Builders\\'
                .($this->segments()->count() > 1
                    ? $this->segments()->slice(0, -1)->implode('\\').'\\'
                    : ''),
            '${Model}' => $this->choices->get('model')->get('name'),
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
        return $this->params->get('root')
            .'app'.DIRECTORY_SEPARATOR
            .'Tables'.DIRECTORY_SEPARATOR
            .'Builders'.DIRECTORY_SEPARATOR
            .$this->segments()->slice(0, -1)
                ->implode(DIRECTORY_SEPARATOR);
    }

    private function templatePath()
    {
        return $this->params->get('root')
            .'app'.DIRECTORY_SEPARATOR
            .'Tables'.DIRECTORY_SEPARATOR
            .'Templates'.DIRECTORY_SEPARATOR
            .$this->segments()->slice(0, -1)
                ->implode(DIRECTORY_SEPARATOR);
    }

    private function controllerPath()
    {
        return $this->params->get('root')
            .'app'.DIRECTORY_SEPARATOR
            .'Http'.DIRECTORY_SEPARATOR
            .'Controllers'.DIRECTORY_SEPARATOR
            .$this->segments()->implode(
                DIRECTORY_SEPARATOR
        );
    }

    private function stub($file)
    {
        return File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'table'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function segments()
    {
        return $this->segments
            ?? $this->segments = collect(
                explode(
                    '.',
                    $this->choices->get('permissionGroup')->get('name')
            )
            )->map(function ($segment) {
                return Str::ucfirst($segment);
            });
    }
}
