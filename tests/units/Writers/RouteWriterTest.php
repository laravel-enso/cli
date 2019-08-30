<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Writers\RoutesWriter;
use LaravelEnso\Cli\tests\Helpers\CliAsserts;

class RouteWriterTest extends TestCase
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
                'name' => 'perm.group',
            ],
            'model' => [
                'name' => 'testModel',
            ],
            'permissions' => [],
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
    public function can_create_route_directory()
    {
        (new RoutesWriter($this->choices, $this->params))->run();

        $this->assertDirectoryExists($this->root.'resources/js/routes/perm/group');
    }

    /** @test */
    public function can_create_index_route()
    {
        $this->choices->get('permissions')->put('index', 'index');

        (new RoutesWriter($this->choices, $this->params))->run();

        $this->assertViewRouteFileContains("name: 'perm.group.index'", 'perm/group/index.js');
        $this->assertViewRouteFileContains('component: TestModelIndex', 'perm/group/index.js');
        $this->assertViewRouteFileContains("title: 'Tests Models'", 'perm/group/index.js');
    }

    /** @test */
    public function can_create_show_route()
    {
        $this->choices->get('permissions')->put('edit', 'edit');

        (new RoutesWriter($this->choices, $this->params))->run();

        $this->assertViewRouteFileContains("name: 'perm.group.edit'", 'perm/group/edit.js');
        $this->assertViewRouteFileContains('component: TestModelEdit', 'perm/group/edit.js');
        $this->assertViewRouteFileContains("title: 'Edit Tests Models'", 'perm/group/edit.js');
    }

    /** @test */
    public function can_create_create_route()
    {
        $this->choices->get('permissions')->put('create', 'create');

        (new RoutesWriter($this->choices, $this->params))->run();

        $this->assertViewRouteFileContains("name: 'perm.group.create'", 'perm/group/create.js');
        $this->assertViewRouteFileContains('component: TestModelCreate', 'perm/group/create.js');
        $this->assertViewRouteFileContains("title: 'Create Tests Models'", 'perm/group/create.js');
    }
}
