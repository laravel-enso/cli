<?php
/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/6/18
 * Time: 3:33 PM
 */

namespace LaravelEnso\StructureManager\tests\unit;


use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\StructureManager\app\Classes\Helpers\Writers\RoutesGenerator;
use Tests\TestCase;

class RoutesGeneratorTest extends TestCase
{
    private $structure;
    private $routesPath;

    /** @test */
    public function test()
    {
        $this->assertTrue(File::exists($this->routesPath));
    }

    public function setUp()
    {
        parent::setUp();

        $this->setupStructure();
        $this->setFilePath();
        $this->generateApiRoutes();
    }

    private function setupStructure(): void
    {
        $this->structure = new Obj((array) json_decode(File::get(__DIR__.'/../../src/app/Commands/stubs/test.stub')));

        collect($this->structure)->keys()
            ->each(function ($choice) {
                $this->structure->set($choice, new Obj((array) $this->structure->get($choice)));
            });
    }

    private function setFilePath()
    {
        $model = $this->structure->get('model')->get('name');

        $this->routesPath = base_path('routes').DIRECTORY_SEPARATOR.strtolower($model).'_routes.php';
    }

    private function generateApiRoutes()
    {
        $apiWriter = new RoutesGenerator($this->structure);
        $apiWriter->run();
    }
}