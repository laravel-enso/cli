<?php

namespace LaravelEnso\StructureManager\app\Writers;

use LaravelEnso\Helpers\app\Classes\Obj;

class RoutesGenerator
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
        [$from, $to] = $this->fromTo();

        return str_replace($from, $to, $this->stub('routes'));
    }

    private function fromTo()
    {
        $model = $this->choices->get('model')->get('name');
        $resourcePrefix = $this->segments->slice(0, -1);

        $array = [
            '${namespace}' => $this->namespace(),
            '${groupPrefix}' => "->prefix('".$this->segments->implode('/')."')->as('".$this->segments->implode('.').".')",
            '${resourcePrefix}' => $resourcePrefix->isNotEmpty()
                ? "->prefix('".$resourcePrefix->implode('/')."')->as('".$resourcePrefix->implode('.').".')"
                : '',
            '${Model}' => $model,
            '${Models}' => str_plural($model),
            '${resource}' => camel_case(str_plural($model)),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function namespace()
    {
        return $this->segments
            ->map(function ($segment) {
                return ucfirst($segment);
            })->implode('\\');
    }

    private function stub($file)
    {
        return \File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'api'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function setSegments()
    {
        $this->segments = collect(
            explode('.', $this->choices->get('permissionGroup')->get('name'))
        );
    }
}
