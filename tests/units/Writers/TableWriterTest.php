<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\tests\Helpers\Cli;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Writers\TableWriter;

class TableWriterTest extends TestCase
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
    public function can_create_template()
    {
        (new TableWriter($this->choices))->handle();

        $this->assertTableTemplateContains([
            '"routePrefix": "group.testModels"',
            '"name": "Test Model"',
            '"data": "test_models.id"',
        ]);
    }

    /** @test */
    public function can_create_builder()
    {
        (new TableWriter($this->choices))->handle();

        $this->assertTableBuilderContains([
            'namespace Namespace\App\Tables\Builders\Group;',
            'class TestModelTable extends Table',
            'test_models.id',
        ]);
    }

    /** @test */
    public function can_create_controller()
    {
        $this->setPermission('initTable');

        (new TableWriter($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'class InitTable extends Controller',
            'use Init;',
            'protected $tableClass = TestModelTable::class;',
        ], 'InitTable');
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'group.testModels',],
            'model' => ['name' => 'testModel',],
            'permissions' => [],
        ]);
    }

    protected function params()
    {
        return new Obj([
            'root' => $this->root,
            'namespace' => 'Namespace\App\\',
        ]);
    }
}
