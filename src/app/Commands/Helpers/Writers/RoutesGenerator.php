<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/6/18
 * Time: 3:28 PM
 */

namespace LaravelEnso\StructureManager\app\Classes\Helpers\Writers;


use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;

class RoutesGenerator
{

    private $structure;
    private $routesPath;

    private $segments;

    public function __construct(Obj $structure)
    {
        $this->structure = $structure;
        $this->setFilePath();
    }

    private function setFilePath()
    {
        $this->routesPath = base_path('routes');
    }

    public function run()
    {
        $this->writeRoutes();
    }

    private function writeRoutes()
    {
        $this->segments = $this->ucfirstPermissionGroupSegments();
        $content = $this->buildRoute(0);

        $model = $this->structure->get('model')->get('name');
        File::put($this->routesPath.DIRECTORY_SEPARATOR.strtolower($model).'_routes.php', $content);
    }

    private function buildRoute($index)
    {
        $result = '';

        if ($index < count($this->segments) - 1) {
            $current = $this->getSegment($index);
            $inner = $this->buildRoute($index + 1);

            $result = $this->replaceInner($inner, $current);
        }

        if ($index === count($this->segments) - 1) {
            $result = $this->getRoutes();
        }

        \Log::debug($result);

        return $result;
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

    private function getSegment($index): string
    {
        $replaceArray = $this->segmentArray($this->segments[$index]);

        $content = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $this->template('segment')
        );

        return $content;
    }

    private function segmentArray($segment)
    {
        return [
            '${Segment}' => $segment,
            '${segment}' => ucfirst($segment),
        ];
    }

    private function template($file)
    {
        return File::get(
            __DIR__
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'..'
            .DIRECTORY_SEPARATOR.'stubs'
            .DIRECTORY_SEPARATOR.'api'
            .DIRECTORY_SEPARATOR.$file.'.stub'
        );
    }

    private function getRoutes()
    {
        $replaceArray = $this->routesArray();

        $content = str_replace(
            array_keys($replaceArray),
            array_values($replaceArray),
            $this->template('routes')
        );

        return $content;
    }

    private function routesArray()
    {
        $model = $this->structure->get('model')->get('name');

        return [
            '${Model}'  => $model,
            '${Models}'  => str_plural($model),
            '${models}' => str_plural(strtolower($model)),
        ];
    }

    private function replaceInner($inner, $current)
    {
        $content = str_replace('${inner}', $inner, $current);
        $padded = str_replace('${padding}', '    ', $content);

        return $padded;
    }
}