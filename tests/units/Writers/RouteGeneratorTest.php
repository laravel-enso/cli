<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\tests\Helpers\CliAsserts;
use LaravelEnso\Cli\app\Writers\RouteGenerator;

class RouteGeneratorTest extends TestCase
{
    use CliAsserts;

    private $root;
    private $params;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp/';

        $this->choices = new Obj([
            'permissionGroup' => [
            ],
            'model' => [
                'name' => 'testModel',
            ],
            'permissions' => [
                'index' => true, 'create' => true, 'store' => true, 'edit' => true,
                'exportExcel' => true, 'destroy' => true, 'initTable' => true,
                'tableData' => true, 'update' => true, 'options' => 'options', 'show' => true,
            ],
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
    public function can_create_route_group()
    {
        $this->choices->get('permissionGroup')->put('name', 'a.b.c');

        $result = (new RouteGenerator($this->choices, $this->params))->run();

        $this->assertContains('Route::namespace(\'A\\B\\C\')', $result);
        $this->assertContains('->prefix(\'a/b/c\')->as(\'a.b.c.\')', $result);
    }

    /** @test */
    public function can_create_routes()
    {
        $result = (new RouteGenerator($this->choices, $this->params))->run();

        $this->assertRoutes($result);
    }

    /** @test */
    public function can_create_routes_for_package()
    {
        $this->choices->get('permissionGroup')->put('name', 'a.b.c');
        $this->choices->put('package', new Obj())->get('package')->put('name', 'testPackage');
        $this->params->put('namespace', 'Namespace\app\\');

        (new RouteGenerator($this->choices, $this->params))->run();

        $this->assertRoutes(File::get($this->root.'routes/api.php'));
        $this->assertFileContains("Route::namespace('Namespace\app\Http\Controllers\A\B\C')", 'routes/api.php');
    }

    /**
     * @param $result
     */
    private function assertRoutes($result): void
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
}
