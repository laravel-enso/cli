<?php

namespace LaravelEnso\StructureManager\app\Writers;

use LaravelEnso\Helpers\app\Classes\Obj;

class TableWriter
{
    private $choices;
    private $segments;

    public function __construct(Obj $choices)
    {
        $this->choices = $choices;
        $this->setSegments();
    }

    public function run()
    {
        $this->createFolders()
            ->writeTemplate()
            ->writeBuilder()
            ->writeController();
    }

    private function createFolders()
    {
        if (!\File::isDirectory($this->builderPath())) {
            \File::makeDirectory($this->builderPath(), 0755, true);
        }

        if (!\File::isDirectory($this->templatePath())) {
            \File::makeDirectory($this->templatePath(), 0755, true);
        }

        if (!\File::isDirectory($this->controllerPath())) {
            \File::makeDirectory($this->controllerPath(), 0755, true);
        }

        return $this;
    }

    private function writeTemplate()
    {
        [$from, $to] = $this->templateFromTo();

        \File::put(
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
            '${Models}' => str_plural($model),
            '${models}' => str_plural(strtolower($model)),
            '${icon}' => $this->choices->get('menu')->get('icon'),
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
            .str_plural(strtolower($this->choices->get('model')->get('name')))
            .'.json';
    }

    private function writeBuilder()
    {
        [$from, $to] = $this->builderFromTo();

        \File::put(
            $this->builderName(),
            str_replace($from, $to, $this->stub('builder'))
        );

        return $this;
    }

    private function builderFromTo()
    {
        $model = $this->choices->get('model')->get('name');

        $array = [
            '${namespace}' => 'App\\Tables\\Builders\\'
                .$this->segments->slice(0, 1)->implode('\\'),
            '${Model}' => $model,
            '${models}' => str_plural(strtolower($model)),
            '${depth}' => str_repeat('../', $this->segments->count() - 1),
            '${relativePath}' => $this->segments->slice(0, -1)->implode('/'),
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

    private function writeController()
    {
        [$from, $to] = $this->controllerFromTo();

        \File::put(
            $this->controllerName(),
            str_replace($from, $to, $this->stub('controller'))
        );

        return $this;
    }

    private function controllerFromTo()
    {
        $array = [
            '${namespace}' => 'App\\Http\\Controllers\\'
                .$this->segments->implode('\\'),
            '${builderNamespace}' => 'App\\Tables\\Builders\\'
                .$this->segments->slice(0, 1)->implode('\\'),
            '${Model}' => $this->choices->get('model')->get('name'),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function controllerName()
    {
        return $this->controllerPath()
            .DIRECTORY_SEPARATOR
            .$this->choices->get('model')->get('name')
            .'TableController.php';
    }

    private function builderPath()
    {
        return app_path(
            'Tables/Builders'.'/'.$this->segments->slice(0, -1)->implode('/')
        );
    }

    private function templatePath()
    {
        return app_path(
            'Tables/Templates'.'/'.$this->segments->slice(0, -1)->implode('/')
        );
    }

    private function controllerPath()
    {
        return app_path(
            'Http/Controllers'.'/'.$this->segments->implode('/')
        );
    }

    private function stub($file)
    {
        return \File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'table'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function setSegments()
    {
        $this->segments = collect(
            explode('.', $this->choices->get('permissionGroup')->get('name'))
        )->map(function ($segment) {
            return ucfirst($segment);
        });
    }
}
