<?php

namespace LaravelEnso\StructureManager\app\Writers;

use LaravelEnso\Helpers\app\Classes\Obj;

class FormWriter
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
            ->writeRequest()
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
        \File::put(
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
            .strtolower($this->choices->get('model')->get('name'))
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
            '${relativePath}' => $this->segments->slice(0, -1)->implode('/'),
            '${namespace}' => 'App\\Forms\\Builders'
                .($this->segments->count() > 1
                    ? '\\'.$this->segments->slice(0, -1)->implode('\\')
                    : ''),
            '${depth}' => str_repeat('../', $this->segments->count()),
            '${model}' => strtolower($model),
            '${Model}' => $model,
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
        \Artisan::call('make:request', [
            'name' => $this->request(),
        ]);

        return $this;
    }

    private function request()
    {
        return $this->segments->slice(0, -1)
            ->implode('\\')
            .'\\'
            .'Validate'
            .$this->choices->get('model')->get('name')
            .'Request';
    }

    private function writeController()
    {
        [$from, $to] = $this->controllerFromTo();

        \File::put(
            $this->controllerName(),
            str_replace($from, $to, $this->stub('controller'))
        );
    }

    private function controllerFromTo()
    {
        $model = $this->choices->get('model')->get('name');

        $array = [
            '${Model}' => $model,
            '${model}' => strtolower($model),
            '${permissionGroup}' => $this->choices->get('permissionGroup')->get('name'),
            '${namespace}' => 'App\\Http\\Controllers\\'.$this->segments->implode('\\'),
            '${builderNamespace}' => 'App\\Forms\\Builders\\'.$this->segments->slice(0, -1)->implode('\\'),
            '${requestNamespace}' => 'App\\Http\\Requests\\'.$this->segments->slice(0, -1)->implode('\\'),
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
            .'Controller.php';
    }

    private function builderPath()
    {
        return app_path(
            'Forms/Builders'.'/'.$this->segments->slice(0, -1)->implode('/')
        );
    }

    private function templatePath()
    {
        return app_path(
            'Forms/Templates'.'/'.$this->segments->slice(0, -1)->implode('/')
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
            __DIR__
            .DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'form'
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
