<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\tests\Helpers\CliAsserts;
use LaravelEnso\Cli\app\Writers\OptionsWriter;

class OptionsWriterTest extends TestCase
{
    use CliAsserts;

    private $root;
    private $choices;
    private $params;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp/';

        $this->choices = new Obj([
            'permissionGroup' => [
                'name' => 'group.testModels',
            ],
            'model' => [
                'name' => 'testModel',
            ],
        ]);

        $this->params = new Obj([
            'root' => $this->root,
            'namespace' => 'Namespace\App\\',
        ]);
    }

    protected function tearDown() :void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_controller()
    {
        (new OptionsWriter($this->choices, $this->params))->run();

        $this->assertControllerContains('namespace Namespace\App\Http\Controllers\Group\TestModels;', 'Options');
        $this->assertControllerContains('class Options extends Controller', 'Options');
        $this->assertControllerContains('protected $model = TestModel::class;', 'Options');
    }
}
