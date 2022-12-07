<?php

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Routes;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\Services\Obj;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp';

        $this->initChoices();
        Segments::ucfirst(false);
        Path::segments();
    }

    protected function tearDown(): void
    {
        // parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_route_directory()
    {
        $this->write(Routes::class);

        $this->assertDirectoryExists($this->path(['client', 'src', 'js', 'routes', 'perm']));
    }

    /** @test */
    public function can_create_index_route()
    {
        $this->setPermission('index');

        $this->write(Routes::class);

        $this->assertViewRouteContains([
            "name: 'perm.group.index'",
            'component: TestModelIndex',
            "title: 'Test Models'",
        ], ['perm', 'group', 'index.js']);
    }

    /** @test */
    public function can_create_show_route()
    {
        $this->setPermission('show');

        $this->write(Routes::class);

        $this->assertViewRouteContains([
            "name: 'perm.group.show'",
            'component: TestModelShow',
            "title: 'Show Test Model'",
        ], ['perm', 'group', 'show.js']);
    }

    /** @test */
    public function can_create_edit_route()
    {
        $this->setPermission('edit');

        $this->write(Routes::class);

        $this->assertViewRouteContains([
            "name: 'perm.group.edit'",
            'component: TestModelEdit',
            "title: 'Edit Test Model'",
        ], ['perm', 'group', 'edit.js']);
    }

    /** @test */
    public function can_create_segment_route()
    {
        $this->setPermission('edit');

        $this->write(Routes::class);

        $this->assertViewRouteContains([
            "const routes = routeImporter(require.context('./perm', false, /.*\.js$/))",
            "path: '/perm'",
        ], 'perm.js');
    }

    /** @test */
    public function can_create_parent_segment_route()
    {
        $this->setPermission('edit');

        $this->write(Routes::class);

        $this->assertViewRouteContains([
            "const routes = routeImporter(require.context('./group', false, /.*\.js$/));",
            "path: 'group'",
            "breadcrumb: 'group'",
            "route: 'perm.group.index'",
        ], ['perm', 'group.js']);
    }

    /** @test */
    public function cannot_create_non_route()
    {
        $this->setPermission('destroy');

        $this->write(Routes::class);

        $this->assertFileDoesNotExist($this->viewRoutePath('perm/group/destroy.js'));
    }

    /** @test */
    public function cannot_create_route_for_false_permission()
    {
        $this->choices->put('permissions', new Collection(['show' => false]));

        $this->write(Routes::class);

        $this->assertFileDoesNotExist($this->viewRoutePath('perm/group/show.js'));
    }

    /** @test */
    public function can_create_create_route()
    {
        $this->setPermission('create');

        $this->write(Routes::class);

        $this->assertViewRouteContains([
            "name: 'perm.group.create'",
            'component: TestModelCreate',
            "title: 'Create Test Model'",
        ], ['perm',  'group', 'create.js']);
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'perm.group'],
            'model'           => ['name' => 'TestModel'],
            'permissions'     => [],
        ]);
    }
}
