<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Table;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\App\Classes\Obj;
use Tests\TestCase;

class TableTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp';

        $this->initChoices();
        Segments::ucfirst();
        Path::segments();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_template()
    {
        $this->write(Table::class);

        $this->assertTableTemplateContains([
            '"routePrefix": "group.testModels"',
            '"name": "Test Model"',
            '"data": "test_models.id"',
        ]);
    }

    /** @test */
    public function can_create_builder()
    {
        $this->write(Table::class);

        $this->assertTableBuilderContains([
            'namespace Namespace\App\Tables\Builders\Group;',
            'class TestModelTable implements Table',
            'test_models.id',
        ]);
    }

    /** @test */
    public function can_create_controller()
    {
        $this->setPermission('initTable');

        $this->write(Table::class);

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'class InitTable extends Controller',
            'use Init;',
            'protected string $tableClass = TestModelTable::class;',
        ], 'InitTable');
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'group.testModels'],
            'model' => ['name' => 'TestModel'],
            'permissions' => [],
        ]);
    }

}
