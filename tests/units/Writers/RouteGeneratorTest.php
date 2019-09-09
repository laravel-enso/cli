<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\tests\Helpers\Cli;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Writers\RouteGenerator;

class RouteGeneratorTest extends TestCase
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
    public function can_create_route_group()
    {
        $result = (new RouteGenerator($this->choices))->handle();

        $this->assertContains("Route::namespace('Perm\Group')", $result);
        $this->assertContains("->prefix('perm/group')->as('perm.group.')", $result);
    }

    /** @test */
    public function can_create_routes()
    {
        $result = (new RouteGenerator($this->choices))->handle();

        $this->assertRoutes($result);
    }

    /** @test */
    public function can_create_routes_for_package()
    {
        $this->choices->put('package', new Obj(['name' => 'testPackage']));
        $this->choices->params()->put('namespace', 'Namespace\app\\');

        (new RouteGenerator($this->choices))->handle();

        $this->assertCliFileContains("Route::namespace('Namespace\app\Http\Controllers\Perm\Group')", 'routes/api.php');
        $this->assertRoutes(File::get($this->root.'routes/api.php'));
    }

    private function assertRoutes($result)
    {
        $this->assertContains("Route::get('', 'Index')->name('index');", $result);
        $this->assertContains("Route::get('create', 'Create')->name('create');", $result);
        $this->assertContains("Route::get('{testModel}/edit', 'Edit')->name('edit');", $result);
        $this->assertContains("Route::get('options', 'Options')->name('options');", $result);
        $this->assertContains("Route::patch('{testModel}', 'Update')->name('update');", $result);
        $this->assertContains("Route::post('', 'Store')->name('store');", $result);
        $this->assertContains("Route::delete('{testModel}', 'Destroy')->name('destroy');", $result);
        $this->assertContains("Route::get('initTable', 'InitTable')->name('initTable');", $result);
        $this->assertContains("Route::get('tableData', 'TableData')->name('tableData');", $result);
        $this->assertContains("Route::get('exportExcel', 'ExportExcel')->name('exportExcel');", $result);
        $this->assertContains("Route::get('{testModel}', 'Show')->name('show');", $result);
    }

    protected function choices(): \LaravelEnso\Helpers\app\Classes\Obj
    {
        return new Obj([
            'permissionGroup' => ['name' => 'perm.group'],
            'model' => ['name' => 'testModel'],
            'permissions' => $this->permissions(),
        ]);
    }

    protected function params(): \LaravelEnso\Helpers\app\Classes\Obj
    {
        return new Obj([
            'root' => $this->root,
        ]);
    }
}
