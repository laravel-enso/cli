<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\app\Writers\RoutesWriter;
use LaravelEnso\Cli\tests\Helpers\Cli;
use LaravelEnso\Helpers\app\Classes\Obj;
use Tests\TestCase;

class RouteWriterTest extends TestCase
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
    public function can_create_route_directory()
    {
        (new RoutesWriter($this->choices))->handle();

        $this->assertDirectoryExists($this->root.'client/src/js/routes/perm/group');
    }

    /** @test */
    public function can_create_index_route()
    {
        $this->setPermission('index');

        (new RoutesWriter($this->choices))->handle();

        $this->assertViewRouteContains([
            "name: 'perm.group.index'",
            'component: TestModelIndex',
            "title: 'Test Models'",
        ], 'perm/group/index.js');
    }

    /** @test */
    public function can_create_show_route()
    {
        $this->setPermission('show');

        (new RoutesWriter($this->choices))->handle();

        $this->assertViewRouteContains([
            "name: 'perm.group.show'",
            'component: TestModelShow',
            "title: 'Show Test Model'"
        ], 'perm/group/show.js');
    }

    /** @test */
    public function can_create_edit_route()
    {
        $this->setPermission('edit');

        (new RoutesWriter($this->choices))->handle();

        $this->assertViewRouteContains([
            "name: 'perm.group.edit'",
            'component: TestModelEdit',
            "title: 'Edit Test Model'"
        ], 'perm/group/edit.js');
    }

    /** @test */
    public function can_create_segment_route()
    {
        $this->setPermission('edit');

        (new RoutesWriter($this->choices))->handle();

        $this->assertViewRouteContains([
            "const routes = routeImporter(require.context('./perm', false, /.*\.js$/))",
            "path: '/perm'",
        ], 'perm.js');
    }

    /** @test */
    public function can_create_parent_segment_route()
    {
        $this->setPermission('edit');

        (new RoutesWriter($this->choices))->handle();

        $this->assertViewRouteContains([
            "const routes = routeImporter(require.context('./group', false, /.*\.js$/));",
            "path: 'group'",
            "breadcrumb: 'group'",
            "route: 'perm.group.index'",
        ], 'perm/group.js');
    }

    /** @test */
    public function cannot_create_non_route()
    {
        $this->setPermission('destroy');

        (new RoutesWriter($this->choices))->handle();

        $this->assertFileNotExists($this->viewRoutePath('perm/group/destroy.js'));
    }

    /** @test */
    public function cannot_create_route_for_false_permission()
    {
        $this->choices->put('permissions', collect(['show' => false]));

        (new RoutesWriter($this->choices))->handle();

        $this->assertFileNotExists($this->viewRoutePath('perm/group/show.js'));
    }

    /** @test */
    public function can_create_create_route()
    {
        $this->setPermission('create');

        (new RoutesWriter($this->choices))->handle();

        $this->assertViewRouteContains([
            "name: 'perm.group.create'",
            'component: TestModelCreate',
            "title: 'Create Test Model'",
        ], 'perm/group/create.js');
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'perm.group'],
            'model' => ['name' => 'testModel'],
            'permissions' => [],
        ]);
    }

    protected function params()
    {
        return new Obj(['root' => $this->root]);
    }
}
