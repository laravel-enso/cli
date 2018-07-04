<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/4/18
 * Time: 4:16 PM.
 */

namespace LaravelEnso\StructureManager\app\Classes\Helpers\Writers;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class PagesWriter
{
    const ROUTES_SEGMENT = 'assets/js/pages';
    const OPERATIONS = ['create', 'edit', 'index', 'show'];

    private $structure;
    private $path;
    private $depth;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;
        $this->setPaths();
        $this->setCrudDepth();
    }

    public function run()
    {
        $this->createFolderStructure();
        $this->writePages();
    }

    private function setPaths()
    {
        $routesPath = str_replace(
            '.',
            DIRECTORY_SEPARATOR,
            $this->structure->get('permissionGroup')->get('name')
        );

        $this->path = resource_path(self::ROUTES_SEGMENT.'/'.$routesPath);
    }

    private function setCrudDepth()
    {
        $segments = explode(
            '.',
            $this->structure->get('permissionGroup')
                ->get('name')
        );

        $this->depth = count($segments);
    }

    private function createFolderStructure()
    {
        if (!File::isDirectory($this->path)) {
            File::makeDirectory($this->path, 0755, true);
        }
    }

    private function writePages()
    {
        collect($this->structure->get('permissions'))
            ->filter(function ($isChosen, $operation) {
                return $isChosen && in_array($operation, self::OPERATIONS);
            })
            ->keys()
            ->each(function ($operation) {
                $this->writePage($operation);
            });
    }

    private function writePage($operation)
    {
        $replaceArray = $this->replaceArray();

        $content = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $this->template($operation)
        );

        $fileName = $this->path.DIRECTORY_SEPARATOR.ucfirst($operation).'.vue';
        File::put($fileName, $content);
    }

    private function replaceArray(): array
    {
        return [
            '${routeName}' => $this->structure->get('permissionGroup')->get('name'),
            '${depth}'     => str_repeat('../', $this->depth),
        ];
    }

    private function template($operation)
    {
        return File::get(
            __DIR__
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'pages'
            .DIRECTORY_SEPARATOR.$operation.'.stub'
        );
    }
}
