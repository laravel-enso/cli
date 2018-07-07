<?php

namespace LaravelEnso\StructureManager\tests\unit;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\StructureManager\app\Classes\Helpers\Writers\RoutesWriter;
use Tests\TestCase;

/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/3/18
 * Time: 10:35 AM.
 */
class RoutesGenerationTest extends TestCase
{
    private $FOLDER_PATH;
    private $structure;

    /** @test */
    public function test()
    {
        $this->assertTrue(File::isDirectory($this->FOLDER_PATH));
    }

    public function setUp()
    {
        parent::setUp();

        $this->setFolderPath();
        $this->setupStructure();
        $this->generateRoutes();
    }

    private function setupStructure(): void
    {
        $this->structure = new Obj((array) json_decode(File::get(__DIR__.'/../../src/app/Commands/stubs/test.stub')));

        collect($this->structure)->keys()
            ->each(function ($choice) {
                $this->structure->set($choice, new Obj((array) $this->structure->get($choice)));
            });
    }

    private function generateRoutes(): void
    {
        $routesWriter = new RoutesWriter($this->structure);
        $routesWriter->run();
    }

    private function setFolderPath()
    {
        $this->FOLDER_PATH = resource_path('assets/js/routes/administration/projects');
    }
}
