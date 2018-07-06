<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/6/18
 * Time: 1:26 PM
 */

namespace LaravelEnso\StructureManager\app\Classes\Helpers\Writers;


use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class ControllerWriter
{
    private $structure;
    private $controllerPath;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;
        $this->setFilePath();
    }

    private function setFilePath()
    {
        $segments = $this->ucfirstPermissionGroupSegments();

        $this->controllerPath = app_path('Http/Controllers/'.implode('/', $segments));
    }

    public function run()
    {
        $this->createFolderStructure();
        $this->writeController();
    }

    private function createFolderStructure()
    {
        if (!File::isDirectory($this->controllerPath)) {
            File::makeDirectory($this->controllerPath, 0755, true);
        }
    }

    private function writeController()
    {
        $model = $this->structure->get('model')->get('name');
        $replaceArray = $this->controllerArray();

        $content = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $this->template('controller')
        );

        File::put($this->controllerPath.DIRECTORY_SEPARATOR.$model.'Controller.php', $content);
    }

    private function controllerArray()
    {
        $model = $this->structure->get('model')->get('name');
        $permissionGroup = $this->structure->get('permissionGroup')->get('name');

        return [
            '${Model}' => $model,
            '${model}' => strtolower($model),
            '${routeSegment}' => $permissionGroup,
            '${namespaceSegment}' => !$this->getNamespaceSegment() ?: $this->getNamespaceSegment().'\\',
            '${controllerNamespaceSegment}' => $this->getControllerNamespaceSegment(),
        ];
    }

    private function getControllerNamespaceSegment()
    {
        $segments = $this->ucfirstPermissionGroupSegments();

        return implode('\\', $segments);
    }

    private function getNamespaceSegment()
    {
        $segments = $this->ucfirstPermissionGroupSegments();

        array_pop($segments);

        return implode('\\', $segments);
    }

    private function ucfirstPermissionGroupSegments(): array
    {
        $permissionGroup = $this->structure->get('permissionGroup')->get('name');

        $segments = collect(explode('.', $permissionGroup))
            ->map(function ($segment) {
                return ucfirst($segment);
            })
            ->toArray();

        return $segments;
    }

    private function template($file)
    {
        return File::get(
            __DIR__
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'controller'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }
}