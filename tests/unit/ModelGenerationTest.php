<?php

namespace LaravelEnso\StructureManager\tests\unit;

use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\StructureManager\app\Classes\Helpers\Writers\ModelAndMigrationWriter;
use Tests\TestCase;

/**
 * Created with luv for spa2.
 * User: mihai
 * Date: 7/3/18
 * Time: 10:35 AM.
 */
class ModelGenerationTest extends TestCase
{
    private $MODEL_FILE;
    private $structure;

    /** @test */
    public function test()
    {
        $this->assertTrue(File::exists($this->MODEL_FILE));
    }

    public function setUp()
    {
        parent::setUp();

        $this->setupStructure();
        $this->setFolderPath();
        $this->generateModel();
    }

    private function setupStructure(): void
    {
        $this->structure = new Obj((array) json_decode(File::get(__DIR__.'/../../src/app/Commands/stubs/test.stub')));

        collect($this->structure)->keys()
            ->each(function ($choice) {
                $this->structure->set($choice, new Obj((array) $this->structure->get($choice)));
            });
    }

    private function generateModel(): void
    {
        $modelWriter = new ModelAndMigrationWriter($this->structure);
        $modelWriter->run();
    }

    private function setFolderPath()
    {
        $model = $this->structure->get('model')->get('name');

        $this->MODEL_FILE = app_path($model.'.php');
    }
}
