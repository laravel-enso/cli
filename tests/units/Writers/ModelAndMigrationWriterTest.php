<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\tests\Helpers\CliAsserts;
use LaravelEnso\Cli\app\Writers\ModelAndMigrationWriter;

class ModelAndMigrationWriterTest extends TestCase
{
    use CliAsserts;

    private $root;
    private $choices;
    private $params;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp/';

        $this->choices = new Obj([
            'model' => [
                'name' => 'testModel',
            ],
            'files' => [],
        ]);

        $this->params = new Obj([
            'root' => $this->root,
        ]);
    }

    protected function tearDown() :void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_model_with_path()
    {
        $this->choices->get('model')->put('path', 'path');

        (new ModelAndMigrationWriter($this->choices, $this->params))->run();

        $this->assertFileExists($this->root.'app/path/TestModel.php');
    }

    /** @test */
    public function can_create_model()
    {
        (new ModelAndMigrationWriter($this->choices, $this->params))->run();

        $this->assertFileExists($this->root.'app/TestModel.php');
        $this->assertFileContains('class TestModel extends Model', 'app/TestModel.php');
    }

    /** @test */
    public function can_create_migration()
    {
        $this->choices->get('files')->put('migration', true);

        Artisan::shouldReceive('call')->with('make:migration', [
            'name' => 'create_test_models_table',
            '--path' => $this->root.'database/migrations',
        ])->once();

        (new ModelAndMigrationWriter($this->choices, $this->params))->run();
    }
}
