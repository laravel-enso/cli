<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\tests\Helpers\Cli;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Writers\StructureMigrationWriter;

class StructureMigrationWriterTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp/';

        $this->initChoices();

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
        (new StructureMigrationWriter($this->choices))->handle();

        $this->assertStructureMigrationContains('class CreateStructureForTestModels extends Migration');
        $this->assertStructureMigrationContains('$permissions = null');
        $this->assertStructureMigrationContains('$menu = null');
        $this->assertStructureMigrationContains('$parentMenu = \'\'');
    }

    /** @test */
    public function can_create_permissions()
    {
        $this->choices->put('permissions', $this->permissions());

        (new StructureMigrationWriter($this->choices))->handle();

        $this->choices->get('permissions')->each(function ($perm) {
            $this->assertStructureMigrationContains("'name' => 'group.testModels.{$perm}'");
        });

        $descriptions = [
            'Show index for test models',
            'Export excel for test models',
            'Init table for test models',
            'Get table data for test models'
        ];

        collect($descriptions)->each(function ($description) {
            $this->assertStructureMigrationContains("'description' => '$description'");
        });
    }

    /** @test */
    public function can_create_menu()
    {
        $this->choices->put('menu', new Obj([
            'parentMenu' => 'parent',
        ]));

        (new StructureMigrationWriter($this->choices))->handle();

        $this->assertStructureMigrationContains("parentMenu = 'parent'");
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'group.testModels'],
            'model' => ['name' => 'testModel'],
        ]);
    }

    protected function params()
    {
        return new Obj(['root' => $this->root]);
    }
}
