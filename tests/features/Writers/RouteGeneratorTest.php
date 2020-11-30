<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\RouteGenerator;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\Services\Obj;
use Tests\TestCase;

class RouteGeneratorTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    public function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp';

        $this->initChoices();

        Segments::ucfirst(false);
        Path::segments();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_route_group()
    {
        (new RouteGenerator($this->choices))->handle();

        $this->assertRouteContains([
            "->prefix('perm/group')",
            "->as('perm.group.')",
        ], ['app', 'perm', 'group.php']);
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

        $this->assertRouteContains(
            ["use Illuminate\Support\Facades\Route;\n\n"],
            ['app', 'perm', 'group.php']
        );

        $this->choices->params()->put('namespace', 'ZZZ');

        $result = (new RouteGenerator($this->choices))->handle();

        $this->assertRouteContains(
            ["\n\nuse Illuminate\Support\Facades\Route;"],
            ['app', 'perm', 'group.php']
        );
    }


    protected function choices(): Obj
    {
        return new Obj([
            'permissionGroup' => ['name' => 'perm.group'],
            'model' => ['name' => 'TestModel'],
            'permissions' => $this->permissions(),
        ]);
    }

    private function assertRoutes($result)
    {
        $this->assertRouteContains([
            "Route::get('create', Create::class)->name('create');",
            "Route::get('{testModel}/edit', Edit::class)->name('edit');",
            "Route::get('options', Options::class)->name('options');",
            "Route::patch('{testModel}', Update::class)->name('update');",
            "Route::post('', Store::class)->name('store');",
            "Route::delete('{testModel}', Destroy::class)->name('destroy');",
            "Route::get('initTable', InitTable::class)->name('initTable');",
            "Route::get('tableData', TableData::class)->name('tableData');",
            "Route::get('exportExcel', ExportExcel::class)->name('exportExcel');",
            "Route::get('{testModel}', Show::class)->name('show');",
        ], ['app', 'perm', 'group.php']);
    }

    private function assertUses($baseNamespace, $result)
    {
        $this->assertRouteContains([
            "use {$baseNamespace}\Index;",
            "use {$baseNamespace}\Edit;",
            "use {$baseNamespace}\Options;",
            "use {$baseNamespace}\Update;",
            "use {$baseNamespace}\Store;",
            "use {$baseNamespace}\Destroy;",
            "use {$baseNamespace}\InitTable;",
            "use {$baseNamespace}\TableData;",
            "use {$baseNamespace}\ExportExcel;",
            "use {$baseNamespace}\Show;",
        ], ['app', 'perm', 'group.php']);
    }
}
