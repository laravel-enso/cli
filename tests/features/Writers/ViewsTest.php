<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Views;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\Services\Obj;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewsTest extends TestCase
{
    use Cli;

    private string $root;
    private Choices $choices;

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
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    #[Test]
    public function can_create_views()
    {
        $this->write(Views::class);

        $this->assertViewPageContains('<enso-table class="box p-0"', 'index');
        $this->assertViewPageContains('<enso-form class="box form-box"/>', 'create');
        $this->assertViewPageContains('<enso-form class="box form-box"/>', 'edit');
        $this->assertViewPageContains('<div/>', 'show');
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
}
