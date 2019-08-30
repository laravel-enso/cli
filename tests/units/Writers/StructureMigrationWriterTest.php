<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\tests\Helpers\CliAsserts;
use LaravelEnso\Cli\app\Writers\StructureMigrationWriter;

class StructureMigrationWriterTest extends TestCase
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
            'permissionGroup' => [
                'name' => 'group.testModels',
            ],
            'model' => [
                'name' => 'testModel',
            ],
        ]);

        $this->params = new Obj([
            'root' => $this->root,
        ]);

        Carbon::setTestNow(Carbon::create(2000, 01, 01, 00));
    }

    protected function tearDown() :void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_empty_migration()
    {
        (new StructureMigrationWriter($this->choices, $this->params))->run();

        $this->assertStructureMigrationContains('class CreateStructureForTestModels extends Migration');
        $this->assertStructureMigrationContains('$permissions = null');
        $this->assertStructureMigrationContains('$menu = null');
        $this->assertStructureMigrationContains('$parentMenu = \'\'');
    }

    /** @test */
    public function can_create_permissions()
    {
        $this->choices->put('permissions', new Obj([
            'index' => 'index', 'create' => 'create', 'store' => 'store', 'edit' => 'edit',
            'exportExcel' => 'exportExcel', 'destroy' => 'destroy', 'initTable' => 'initTable',
            'tableData' => 'tableData', 'update' => 'update', 'options' => 'options', 'show' => 'show',
        ]));

        (new StructureMigrationWriter($this->choices, $this->params))->run();

        $this->choices->get('permissions')->each(function ($perm) {
            $this->assertStructureMigrationContains("'name' => 'group.testModels.{$perm}'");
        });
    }

    /** @test */
    public function can_create_menu()
    {
        $this->choices->put('menu', new Obj([
            'parentMenu' => 'parent',
        ]));

        (new StructureMigrationWriter($this->choices, $this->params))->run();

        $this->assertStructureMigrationContains("parentMenu = 'parent'");
    }
}
