<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Views;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\App\Classes\Obj;
use Tests\TestCase;

class ViewsTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp/';

        $this->initChoices();
        Segments::ucfirst(false);
        Path::segments();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_views()
    {
        (new Views($this->choices))->handle();

        $this->choices->get('permissions')->each(fn ($perm) => (
            $this->assertViewPageContains("name: '".ucfirst($perm)."',", $perm)
        ));
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'group.testModels'],
            'model' => ['name' => 'TestModel'],
            'permissions' => $this->permissions()
                ->only(['index', 'create', 'show', 'edit']),
        ]);
    }

    protected function params()
    {
        return new Obj([
            'root' => $this->root,
            'namespace' => 'Namespace\App',
        ]);
    }
}
