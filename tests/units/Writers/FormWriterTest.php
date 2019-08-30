<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Writers\FormWriter;
use LaravelEnso\Cli\tests\Helpers\CliAsserts;

class FormWriterTest extends TestCase
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
                'namespace' => 'Classes',
            ],
            'permissions' => [],
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
    public function can_create_builder()
    {
        (new FormWriter($this->choices, $this->params))->run();

        $this->assertFormBuilderContains('class TestModelForm');
        $this->assertFormBuilderContains('public function edit(TestModel $testModel)');
        $this->assertFormBuilderContains('return $this->form->edit($testModel);');
    }

    /** @test */
    public function can_create_controller()
    {
        $this->choices->put('permissions', new Obj(['edit' => 'p1', 'nonStandard' => 'p2']));

        (new FormWriter($this->choices, $this->params))->run();

        $this->assertControllerContains('class Edit extends Controller', 'Edit');
        $this->assertControllerContains('public function __invoke(TestModel $testModel, TestModelForm $form)',
            'Edit');
    }

    /** @test */
    public function can_create_index()
    {
        $this->choices->put('permissions', new Obj(['index' => 'index']));

        (new FormWriter($this->choices, $this->params))->run();

        $this->assertControllerContains('namespace Namespace\App\Http\Controllers\Group\TestModels;', 'Index');
        $this->assertControllerContains('class Index extends Controller', 'Index');
        $this->assertControllerContains('public function __invoke(Request $request)', 'Index');
    }

    /** @test */
    public function can_create_show()
    {
        $this->choices->put('permissions', new Obj(['show' => 'show']));

        (new FormWriter($this->choices, $this->params))->run();

        $this->assertControllerContains('namespace Namespace\App\Http\Controllers\Group\TestModels;', 'Show');
        $this->assertControllerContains('use Classes\\TestModel;', 'Show');
        $this->assertControllerContains('class Show extends Controller', 'Show');
        $this->assertControllerContains('public function __invoke(TestModel $testModel)', 'Show');
        $this->assertControllerContains('return [\'testModel\' => $testModel]', 'Show');
    }

    /** @test */
    public function can_create_create()
    {
        $this->choices->put('permissions', new Obj(['create' => 'create']));

        (new FormWriter($this->choices, $this->params))->run();

        $this->assertControllerContains('namespace Namespace\App\Http\Controllers\Group\TestModels;', 'Create');
        $this->assertControllerContains('class Create extends Controller', 'Create');
        $this->assertControllerContains('public function __invoke(TestModelForm $form)', 'Create');
        $this->assertControllerContains('return [\'form\' => $form->create()]', 'Create');
    }

    /** @test */
    public function can_create_destroy()
    {
        $this->choices->put('permissions', new Obj(['destroy' => 'destroy']));

        (new FormWriter($this->choices, $this->params))->run();

        $this->assertControllerContains('namespace Namespace\App\Http\Controllers\Group\TestModels;', 'Destroy');
        $this->assertControllerContains('use Classes\\TestModel;', 'Destroy');
        $this->assertControllerContains('class Destroy extends Controller', 'Destroy');
        $this->assertControllerContains('public function __invoke(TestModel $testModel)',
            'Destroy');
        $this->assertControllerContains("'message' => __('The test model was successfully deleted')",
            'Destroy');
    }

    /** @test */
    public function can_create_update()
    {
        $this->choices->put('permissions', new Obj(['update' => 'update']));

        (new FormWriter($this->choices, $this->params))->run();

        $this->assertControllerContains('namespace Namespace\App\Http\Controllers\Group\TestModels;', 'Update');
        $this->assertControllerContains('use Classes\TestModel;', 'Update');
        $this->assertControllerContains('use Namespace\App\Http\Requests\Group\TestModels\ValidateTestModelUpdate', 'Update');
        $this->assertControllerContains('class Update extends Controller', 'Update');
        $this->assertControllerContains('public function __invoke(ValidateTestModelUpdate $request, TestModel $testModel)',
            'Update');
        $this->assertControllerContains("return ['message' => __('The test model was successfully updated')]",
            'Update');
    }

    /** @test */
    public function can_create_store()
    {
        $this->choices->put('permissions', new Obj(['store' => 'store']));

        (new FormWriter($this->choices, $this->params))->run();

        $this->assertControllerContains('namespace Namespace\App\Http\Controllers\Group\TestModels;', 'Store');
        $this->assertControllerContains('use Classes\TestModel;', 'Store');
        $this->assertControllerContains('use Namespace\App\Http\Requests\Group\TestModels\ValidateTestModelStore', 'Store');
        $this->assertControllerContains('class Store extends Controller', 'Store');
        $this->assertControllerContains('tap($testModel)->fill($request->validated())', 'Store');
        $this->assertControllerContains('public function __invoke(ValidateTestModelStore $request, TestModel $testModel)',
            'Store');
        $this->assertControllerContains("'message' => __('The test model was successfully created')", 'Store');
    }
}
