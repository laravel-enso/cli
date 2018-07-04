<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/3/18
 * Time: 10:20 AM.
 */

namespace LaravelEnso\StructureManager\app\Classes\Helpers\Writers;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class RoutesWriter
{
    const ROUTES_SEGMENT = 'assets/js/routes';
    const OPERATIONS = ['create', 'edit', 'index', 'show'];

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
        $this->createFolderStructure();
        $this->writeCrudRoutes();
        $this->writeSegmentRoutes();
    }

    private function createFolderStructure()
    {
        if (!File::isDirectory($this->crudPath)) {
            File::makeDirectory($this->crudPath, 0755, true);
        }
    }

    private function writeCrudRoutes()
    {
        collect($this->structure->get('permissions'))
            ->filter(function ($chosen, $operation) {
                return $chosen && in_array($operation, self::OPERATIONS);
            })->keys()
            ->each(function ($operation) {
                $this->writeCrudRoute($operation);
            });
    }

    private function writeSegmentRoutes()
    {
        $segments = explode(
            '.',
            $this->structure->get('permissionGroup')
                ->get('name')
        );

        collect($segments)->each(function ($segment, $depth) {
            $this->writeSegmentRoute($segment, $depth);
        });
    }

    private function writeSegmentRoute($segment, $depth)
    {
        $segmentFilePath = $this->segmentPath.DIRECTORY_SEPARATOR.$segment.'.js';

        if (!File::exists($segmentFilePath)) {
            $replaceArray = $this->segmentArray($segment, $depth);

            $content = str_replace(
                array_keys($replaceArray),
                array_values($replaceArray),
                $this->template('segment')
            );

            File::put($segmentFilePath, $content);
        }

        $this->segmentPath .= DIRECTORY_SEPARATOR.$segment;
    }

    private function writeCrudRoute($operation)
    {
        $replaceArray = $this->crudArray();

        $content = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $this->template($operation)
        );

        File::put($this->crudPath.DIRECTORY_SEPARATOR.$operation.'.js', $content);
    }

    private function setPaths()
    {
        $routesPath = str_replace(
            '.',
            DIRECTORY_SEPARATOR,
            $this->structure->get('permissionGroup')
                ->get('name')
        );

        $this->crudPath = resource_path(self::ROUTES_SEGMENT.'/'.$routesPath);

        $this->segmentPath = resource_path(self::ROUTES_SEGMENT);
    }

    private function setCrudDepth()
    {
        $segments = explode(
            '.',
            $this->structure->get('permissionGroup')
                ->get('name')
        );

        $this->crudDepth = count($segments);
    }

    private function template($operation)
    {
        return File::get(
            __DIR__
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'routes'
            .DIRECTORY_SEPARATOR.$operation.'.stub'
        );
    }

    private function crudArray()
    {
        $permissionGroup = $this->structure->get('permissionGroup')->get('name');

        return [
            '${Model}'       => $this->structure->get('model')->get('name'),
            '${pathSegment}' => str_replace('.', DIRECTORY_SEPARATOR, $permissionGroup),
            '${nameSegment}' => $permissionGroup,
            '${depth}'       => str_repeat('../', $this->crudDepth),
        ];
    }

    private function segmentArray($segment, $depth)
    {
        return [
            '${segment}' => $segment,
            '${depth}'   => str_repeat('../', $depth),
        ];
    }
}
