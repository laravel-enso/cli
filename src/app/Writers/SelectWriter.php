<?php

namespace LaravelEnso\StructureManager\app\Writers;

use LaravelEnso\Helpers\app\Classes\Obj;

class SelectWriter
{
    private $structure;
    private $segments;
    private $path;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;

        $this->setSegments();

        $this->path = app_path('Http/Controllers/'.$this->segments->implode('/'));
    }

    public function run()
    {
        $this->createFolders();
        $this->write();
    }

    private function createFolders()
    {
        if (!\File::isDirectory($this->path)) {
            \File::makeDirectory($this->path, 0755, true);
        }
    }

    private function write()
    {
        [$from, $to] = $this->fromTo();

        \File::put(
            $this->filename(),
            str_replace($from, $to, $this->stub('controller'))
        );
    }

    private function fromTo()
    {
        $array = [
            '${namespace}' => 'App\\Http\\Controllers\\'.$this->segments->implode('\\'),
            '${Model}' => $this->structure->get('model')->get('name'),
        ];

        return [
            array_keys($array),
            array_values($array),
        ];
    }

    private function filename()
    {
        return $this->path.DIRECTORY_SEPARATOR
            .$this->structure->get('model')->get('name')
            .'SelectController.php';
    }

    private function stub($file)
    {
        return \File::get(
            __DIR__.DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'select'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function setSegments()
    {
        $this->segments = collect(
            explode('.', $this->structure->get('permissionGroup')->get('name'))
        )->map(function ($segment) {
            return ucfirst($segment);
        });
    }
}
