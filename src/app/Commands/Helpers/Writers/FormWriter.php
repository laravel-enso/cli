<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/5/18
 * Time: 4:13 PM
 */

namespace LaravelEnso\StructureManager\app\Classes\Helpers\Writers;


use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class FormWriter
{

    private $structure;
    private $builderPath;
    private $templatePath;
    private $depth;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;
        $this->setFilePaths();
        $this->setDepth();
    }

    private function setDepth()
    {
        $segments = explode(
            '.',
            $this->structure->get('permissionGroup')
                ->get('name')
        );

        $this->depth = count($segments);
    }

    private function setFilePaths()
    {
        $permissionGroup = $this->structure->get('permissionGroup')->get('name');

        $segments = collect(explode('.', $permissionGroup))
            ->map(function ($segment) {
                return ucfirst($segment);
            })
            ->toArray();

        array_pop($segments);

        $this->builderPath = app_path('Forms/Builders/'.implode('/', $segments));
        $this->templatePath = app_path('Forms/Templates/'.implode('/', $segments));
    }

    public function run()
    {
        $this->createFolderStructure();
        $this->writeTemplate();
        $this->writeBuilder();
    }

    private function createFolderStructure()
    {
        if (!File::isDirectory($this->builderPath)) {
            File::makeDirectory($this->builderPath, 0755, true);
        }

        if (!File::isDirectory($this->templatePath)) {
            File::makeDirectory($this->templatePath, 0755, true);
        }
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

        File::put($this->templatePath.DIRECTORY_SEPARATOR.strtolower($model).'.json', $content);
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

        File::put($this->builderPath.DIRECTORY_SEPARATOR.$model.'Form.php', $content);
    }

    private function template($file)
    {
        return File::get(
            __DIR__
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'form'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function templateArray()
    {
        $permissionGroup = $this->structure->get('permissionGroup')->get('name');

        return [
            '${nameSegment}' => $permissionGroup,
        ];
    }

    private function builderArray()
    {
        $model = $this->structure->get('model')->get('name');

        return [
            '${pathSegment}' => $this->getPathSegment(),
            '${namespaceSegment}' => $this->getNamespaceSegment(),
            '${depth}' => str_repeat('../', $this->depth),
            '${model}' => strtolower($model),
            '${Model}' => $model,
        ];
    }

    private function getNamespaceSegment()
    {
        $permissionGroup = $this->structure->get('permissionGroup')->get('name');

        $segments = collect(explode('.', $permissionGroup))
            ->map(function ($segment) {
                return ucfirst($segment);
            })
            ->toArray();

        array_pop($segments);

        return implode('\\', $segments);
    }

    private function getPathSegment(): string
    {
        $permissionGroup = $this->structure->get('permissionGroup')->get('name');

        $segments = collect(explode('.', $permissionGroup))
            ->map(function ($segment) {
                return ucfirst($segment);
            })
            ->toArray();

        array_pop($segments);

        return str_replace('.', DIRECTORY_SEPARATOR, $permissionGroup);
}
}