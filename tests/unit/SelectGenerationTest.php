<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/6/18
 * Time: 11:58 AM.
 */

namespace LaravelEnso\StructureManager\tests\unit;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\StructureManager\app\Classes\Helpers\Writers\SelectWriter;
use Tests\TestCase;

class SelectGenerationTest extends TestCase
{
    private $structure;
    private $controllerPath;

    /** @test */
    public function testSelectCreation()
    {
        $this->assertTrue(File::exists($this->controllerPath));
    }

    public function setUp()
    {
        parent::setUp();

        $this->setupStructure();
        $this->setFolderPath();
        $this->generateController();
    }

    private function setupStructure(): void
    {
        $this->structure = new Obj((array) json_decode(File::get(__DIR__.'/../../src/app/Commands/stubs/test.stub')));

        collect($this->structure)->keys()
            ->each(function ($choice) {
                $this->structure->set($choice, new Obj((array) $this->structure->get($choice)));
            });
    }

    private function setFolderPath()
    {
        $model = $this->structure->get('model')->get('name');
        $permissionGroup = $this->structure->get('permissionGroup')->get('name');

        $segments = $this->getControllerNamespaceSegments($permissionGroup);

        $this->controllerPath = app_path('Http/Controllers/'.implode('/', $segments).'/'.$model.'SelectController.php');
    }

    private function getControllerNamespaceSegments($permissionGroup)
    {
        $segments = collect(explode('.', $permissionGroup))
            ->map(function ($segment) {
                return ucfirst($segment);
            })
            ->toArray();

        return $segments;
    }

    private function generateController()
    {
        $selectWriter = new SelectWriter($this->structure);
        $selectWriter->run();
    }
}
