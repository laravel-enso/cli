<?php

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\Services\StubWriters\Writer;
use LaravelEnso\Cli\Services\Writers\EnsoStructure;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\Services\Obj;
use Tests\TestCase;

class EnsoStructureTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp';

        $this->initChoices();

        Path::segments(false);

        Carbon::setTestNow(Carbon::create(2000, 01, 01, 00));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_empty_migration()
    {
        (new Writer(new EnsoStructure($this->choices)))->handle();

        $this->assertStructureMigrationContains('class CreateStructureForTestModels extends Migration');
        $this->assertStructureMigrationContains('$permissions = null');
        $this->assertStructureMigrationContains('$menu = []');
        $this->assertStructureMigrationContains('$parentMenu = null');
    }

    /** @test */
    public function can_create_permissions()
    {
        $this->choices->put('permissions', $this->permissions());

        (new Writer(new EnsoStructure($this->choices)))->handle();

        $this->choices->get('permissions')->each(fn ($perm) => $this
            ->assertStructureMigrationContains("'name' => 'group.testModels.{$perm}'"));

        $descriptions = [
            'Show index for test models',
            'Export excel for test models',
            'Init table for test models',
            'Get table data for test models',
        ];

        Collection::wrap($descriptions)->each(fn ($description) => $this
            ->assertStructureMigrationContains("'description' => '{$description}'"));
    }

    /** @test */
    public function can_create_menu()
    {
        $this->choices->put('menu', new Obj(['parentMenu' => 'parent']));

        (new Writer(new EnsoStructure($this->choices)))->handle();

        $this->assertStructureMigrationContains("parentMenu = 'parent'");
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'group.testModels'],
            'model'           => ['name' => 'TestModel'],
        ]);
    }
}
