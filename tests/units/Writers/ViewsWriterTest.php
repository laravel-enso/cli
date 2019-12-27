<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\app\Writers\ViewsWriter;
use LaravelEnso\Cli\tests\Helpers\Cli;
use LaravelEnso\Helpers\app\Classes\Obj;
use Tests\TestCase;

class ViewsWriterTest extends TestCase
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

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_views()
    {
        (new ViewsWriter($this->choices))->handle();

        $this->choices->get('permissions')->each(fn($perm) => (
            $this->assertViewPageContains("name: '".ucfirst($perm)."',", $perm)
        ));
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'group.testModels'],
            'model' => ['name' => 'testModel'],
            'permissions' => $this->permissions()
                ->only(['index', 'create', 'show', 'edit']),
        ]);
    }

    protected function params()
    {
        return new Obj([
            'root' => $this->root,
        ]);
    }
}
