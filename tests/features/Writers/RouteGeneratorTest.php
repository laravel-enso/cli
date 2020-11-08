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
    public function can_create_imports()
    {
        $result = (new RouteGenerator($this->choices))->handle();

        $this->assertUses('Namespace\App\Http\Controllers\Perm\Group', $result);
    }

    /** @test */
    public function can_create_imports_sorted()
    {
        $this->choices->params()->put('namespace', 'AAA');

        $result = (new RouteGenerator($this->choices))->handle();

        $this->assertStringContainsString("use Illuminate\Support\Facades\Route;\n\n", $result);

        $this->choices->params()->put('namespace', 'ZZZ');

        $result = (new RouteGenerator($this->choices))->handle();

        $this->assertStringContainsString("\n\nuse Illuminate\Support\Facades\Route;", $result);
    }

    private function assertRoutes($result)
    {
        $this->assertStringContainsString("Route::get('create', Create::class)->name('create');", $result);
        $this->assertStringContainsString("Route::get('{testModel}/edit', Edit::class)->name('edit');", $result);
        $this->assertStringContainsString("Route::get('options', Options::class)->name('options');", $result);
        $this->assertStringContainsString("Route::patch('{testModel}', Update::class)->name('update');", $result);
        $this->assertStringContainsString("Route::post('', Store::class)->name('store');", $result);
        $this->assertStringContainsString("Route::delete('{testModel}', Destroy::class)->name('destroy');", $result);
        $this->assertStringContainsString("Route::get('initTable', InitTable::class)->name('initTable');", $result);
        $this->assertStringContainsString("Route::get('tableData', TableData::class)->name('tableData');", $result);
        $this->assertStringContainsString("Route::get('exportExcel', ExportExcel::class)->name('exportExcel');", $result);
        $this->assertStringContainsString("Route::get('{testModel}', Show::class)->name('show');", $result);
    }

    private function assertUses($baseNamespace, $result)
    {
        $this->assertStringContainsString("use {$baseNamespace}\Index;", $result);
        $this->assertStringContainsString("use {$baseNamespace}\Edit;", $result);
        $this->assertStringContainsString("use {$baseNamespace}\Options;", $result);
        $this->assertStringContainsString("use {$baseNamespace}\Update;", $result);
        $this->assertStringContainsString("use {$baseNamespace}\Store;", $result);
        $this->assertStringContainsString("use {$baseNamespace}\Destroy;", $result);
        $this->assertStringContainsString("use {$baseNamespace}\InitTable;", $result);
        $this->assertStringContainsString("use {$baseNamespace}\TableData;", $result);
        $this->assertStringContainsString("use {$baseNamespace}\ExportExcel;", $result);
        $this->assertStringContainsString("use {$baseNamespace}\Show;", $result);
    }

    protected function choices(): Obj
    {
        return new Obj([
            'permissionGroup' => ['name' => 'perm.group'],
            'model' => ['name' => 'TestModel'],
            'permissions' => $this->permissions(),
        ]);
    }
}
