<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\app\Writers\ModelAndMigrationWriter;
use LaravelEnso\Cli\tests\Helpers\Cli;
use LaravelEnso\Helpers\app\Classes\Obj;
use Tests\TestCase;

class ModelAndMigrationWriterTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp/';

        $this->initChoices();
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

        (new ModelAndMigrationWriter($this->choices))->handle();

        $this->assertFileExists($this->root.'app/path/TestModel.php');
    }

    /** @test */
    public function can_create_model()
    {
        (new ModelAndMigrationWriter($this->choices))->handle();

        $this->assertCliFileContains('class TestModel extends Model', 'app/TestModel.php');
    }

    /** @test */
    public function can_create_migration()
    {
        $this->choices->get('files')->put('migration', true);

        (new ModelAndMigrationWriter($this->choices))->handle();

        $this->assertMigrationCreated('create_test_models_table');
    }

    protected function choices()
    {
        return new Obj([
            'model' => ['name' => 'testModel'],
            'files' => [],
        ]);
    }

    protected function params()
    {
        return new Obj([
            'root' => $this->root,
        ]);
    }
}
