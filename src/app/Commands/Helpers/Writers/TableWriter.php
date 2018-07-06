<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/6/18
 * Time: 10:24 AM.
 */

namespace LaravelEnso\StructureManager\app\Classes\Helpers\Writers;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class TableWriter
{
    private $structure;
    private $builderPath;
    private $templatePath;
    private $controllerPath;
    private $depth;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;
        $this->setFilePaths();
        $this->setDepth();
    }

    public function run()
    {
        $this->createFolderStructure();
        $this->writeBuilder();
        $this->writeTemplate();
        $this->writeController();
    }

    private function setFilePaths()
    {
        $segments = $this->ucfirstPermissionGroupSegments();
        array_pop($segments);

        $this->builderPath = app_path('Tables/Builders/'.implode('/', $segments));
        $this->templatePath = app_path('Tables/Templates/'.implode('/', $segments));
        $this->controllerPath = app_path('Http/Controllers/'.implode('/', $this->ucfirstPermissionGroupSegments()));
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

    private function setDepth()
    {
        $segments = explode(
            '.',
            $this->structure->get('permissionGroup')
                ->get('name')
        );

        $this->depth = count($segments) - 1;
    }

    private function createFolderStructure()
    {
        if (!File::isDirectory($this->builderPath)) {
            File::makeDirectory($this->builderPath, 0755, true);
        }

        if (!File::isDirectory($this->templatePath)) {
            File::makeDirectory($this->templatePath, 0755, true);
        }

        if (!File::isDirectory($this->controllerPath)) {
            File::makeDirectory($this->controllerPath, 0755, true);
        }
    }

    private function writeBuilder()
    {
        $model = $this->structure->get('model')->get('name');
        $replaceArray = $this->builderArray();

        $content = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $this->template('builder')
        );

        File::put($this->builderPath.DIRECTORY_SEPARATOR.$model.'Table.php', $content);
    }

    private function writeTemplate()
    {
        $model = $this->structure->get('model')->get('name');
        $replaceArray = $this->templateArray();

        $content = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $this->template('template')
        );

        File::put($this->templatePath.DIRECTORY_SEPARATOR.str_plural(strtolower($model)).'.json', $content);
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

        File::put($this->controllerPath.DIRECTORY_SEPARATOR.$model.'TableController.php', $content);
    }

    private function builderArray(): array
    {
        $model = $this->structure->get('model')->get('name');

        return [
            '${namespaceSegment}' => !$this->getNamespaceSegment() ?: '\\'.$this->getNamespaceSegment(),
            '${Model}'            => $model,
            '${models}'           => str_plural(strtolower($model)),
            '${depth}'            => str_repeat('../', $this->depth),
            '${pathSegment}'      => $this->getPathSegment(),
        ];
    }

    private function getNamespaceSegment()
    {
        $segments = $this->ucfirstPermissionGroupSegments();

        array_pop($segments);

        return implode('\\', $segments);
    }

    private function getPathSegment()
    {
        $segments = $this->ucfirstPermissionGroupSegments();

        array_pop($segments);

        return implode('/', $segments);
    }

    private function template($file)
    {
        return File::get(
            __DIR__
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'table'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function templateArray()
    {
        $model = $this->structure->get('model')->get('name');

        return [
            '${route}'  => $this->structure->get('permissionGroup')->get('name'),
            '${Models}' => str_plural($model),
            '${icon}'   => $this->menuIcon(),
        ];
    }

    private function menuIcon()
    {
        return $this->structure->get('menu')
            ? $this->structure->get('menu')->get('icon')
            : '';
    }

    private function controllerArray()
    {
        $model = $this->structure->get('model')->get('name');

        return [
            '${controllerNamespaceSegment}' => $this->getControllerNamespaceSegment(),
            '${namespaceSegment}'           => $this->getNamespaceSegment(),
            '${Model}'                      => $model,
        ];
    }

    private function getControllerNamespaceSegment()
    {
        $segments = $this->ucfirstPermissionGroupSegments();

        return implode('\\', $segments);
    }
}
