<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/3/18
 * Time: 10:20 AM.
 */

namespace LaravelEnso\StructureManager\app\Classes\Helpers;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class RoutesWriter
{
    const ROUTES_SEGMENT = 'assets/js/routes';
    const CRUD_OPERATIONS = ['create', 'edit', 'index', 'show'];

    private $structure;
    private $crudPath;
    private $crudDepth;
    private $segmentPath;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;
        $this->setPaths();
        $this->setCrudDepth();
    }

    public function run()
    {
        //create folder structure
        $this->createFolderStructure();
        $this->writeCrudRoutes();
        $this->writeSegmentRoutes();
    }

    private function createFolderStructure()
    {
        if (!File::isDirectory($this->crudPath)) {
            //directory does not exist, create it.
            File::makeDirectory($this->crudPath, 0777, true);
        }
    }

    private function writeCrudRoutes()
    {

        //filter operations of interest & selected by the user
        $ops = collect($this->structure->permissions->toArray());
        $validOps = $ops->filter(function ($value, $key) {
            return in_array($key, self::CRUD_OPERATIONS)
                && $value;
        });

        foreach ($validOps as $operation => $value) {
            $this->writeCrudRoute($operation);
        }
    }

    private function writeSegmentRoutes()
    {
        $segments = explode('.', $this->structure->permissionGroup->name);
        $finalSegment = array_pop($segments);

        $depth = 0;
        foreach ($segments as $segment) {
            $this->writeSegmentRoute($segment, $depth);
            $depth++;
        }

        $this->writeFinalSegmentRoute($finalSegment, $depth);
    }

    private function writeSegmentRoute($segment, $depth)
    {
        $segmentFilePath = $this->segmentPath.DIRECTORY_SEPARATOR.$segment.'.js';

        if (!File::exists($segmentFilePath)) {
            $template = $this->readTemplate('segment');
            $replaceArray = $this->buildSegmentArray($segment, $depth);

            $content = str_replace(
                array_keys($replaceArray),
                array_values($replaceArray),
                $template
            );

            File::put($segmentFilePath, $content);
        }

        $this->segmentPath .= DIRECTORY_SEPARATOR.$segment;
    }

    private function writeFinalSegmentRoute($segment, $depth)
    {
        $route = $this->structure->menu->link;

        $template = $this->readTemplate('index');
        $replaceArray = $this->buildSegmentArray($segment, $depth, $route);

        $content = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $template
        );

        File::put($this->segmentPath.DIRECTORY_SEPARATOR.$segment.'.js', $content);
    }

    private function writeCrudRoute($operation)
    {
        $template = $this->readTemplate('crud'.DIRECTORY_SEPARATOR.$operation);
        $replaceArray = $this->buildCrudArray();

        //replace tokens
        $content = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $template
        );

        File::put($this->crudPath.DIRECTORY_SEPARATOR.$operation.'.js', $content);
    }

    private function setPaths()
    {
        $routesPath = str_replace('.', DIRECTORY_SEPARATOR, $this->structure->permissionGroup->name);
        $this->crudPath = resource_path(self::ROUTES_SEGMENT.DIRECTORY_SEPARATOR.$routesPath);
        $this->segmentPath = resource_path(self::ROUTES_SEGMENT);
    }

    private function setCrudDepth()
    {
        $segments = explode('.', $this->structure->permissionGroup->name);
        $this->crudDepth = count($segments);
    }

    private function readTemplate($stub)
    {
        return File::get(__DIR__.'/../stubs/routes/'.$stub.'.stub');
    }

    private function buildCrudArray()
    {
        return [
            '${Model}'       => $this->structure->model->name,
            '${Models}'      => $this->getModelsString(),
            '${pathSegment}' => str_replace('.', DIRECTORY_SEPARATOR, $this->structure->permissionGroup->name),
            '${nameSegment}' => $this->structure->permissionGroup->name,
            '${depth}'       => str_repeat('../', $this->crudDepth),
        ];
    }

    private function getModelsString()
    {
        return str_plural($this->structure->model->name);
    }

    private function buildSegmentArray($segment, $depth, $route = '')
    {
        return [
            '${segment}' => $segment,
            '${depth}'   => str_repeat('../', $depth),
            '${route}'   => $route,
        ];
    }
}
