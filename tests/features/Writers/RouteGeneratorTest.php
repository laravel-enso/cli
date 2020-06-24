<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\Services\Writers\RouteGenerator;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\Services\Obj;
use Tests\TestCase;

class RouteGeneratorTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp';

        $this->initChoices();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_route_group()
    {
        $result = (new RouteGenerator($this->choices))->handle();

        $this->assertStringContainsString("Route::namespace('Perm\Group')", $result);
        $this->assertStringContainsString("->prefix('perm/group')", $result);
        $this->assertStringContainsString("->as('perm.group.')", $result);
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
        $this->choices->params()->put('namespace', 'Namespace\App');

        (new RouteGenerator($this->choices))->handle();

        $this->assertCliFileContains(
            "Route::namespace('Namespace\App\Http\Controllers\Perm\Group')",
            ['routes', 'api.php']);

        $this->assertRoutes(File::get("{$this->root}/routes/api.php"));
    }

    private function assertRoutes($result)
    {
        $this->assertStringContainsString("Route::get('', 'Index')->name('index');", $result);
        $this->assertStringContainsString("Route::get('create', 'Create')->name('create');", $result);
        $this->assertStringContainsString("Route::get('{testModel}/edit', 'Edit')->name('edit');", $result);
        $this->assertStringContainsString("Route::get('options', 'Options')->name('options');", $result);
        $this->assertStringContainsString("Route::patch('{testModel}', 'Update')->name('update');", $result);
        $this->assertStringContainsString("Route::post('', 'Store')->name('store');", $result);
        $this->assertStringContainsString("Route::delete('{testModel}', 'Destroy')->name('destroy');", $result);
        $this->assertStringContainsString("Route::get('initTable', 'InitTable')->name('initTable');", $result);
        $this->assertStringContainsString("Route::get('tableData', 'TableData')->name('tableData');", $result);
        $this->assertStringContainsString("Route::get('exportExcel', 'ExportExcel')->name('exportExcel');", $result);
        $this->assertStringContainsString("Route::get('{testModel}', 'Show')->name('show');", $result);
    }

    private function choices(): Obj
    {
        return new Obj([
            'permissionGroup' => ['name' => 'perm.group'],
            'model' => ['name' => 'TestModel'],
            'permissions' => $this->permissions(),
        ]);
    }
}
