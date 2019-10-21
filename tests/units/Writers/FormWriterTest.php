<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\tests\Helpers\Cli;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Writers\FormWriter;

class FormWriterTest extends TestCase
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
    public function can_create_builder()
    {
        (new FormWriter($this->choices))->handle();

        $this->assertFormBuilderContains([
            'class TestModelForm',
            'public function edit(TestModel $testModel)',
            'return $this->form->edit($testModel);'
        ]);
    }

    /** @test */
    public function can_create_controller()
    {
        $this->setPermission('edit');

        (new FormWriter($this->choices))->handle();

        $this->assertControllerContains([
            'class Edit extends Controller',
            'public function __invoke(TestModel $testModel, TestModelForm $form)',
        ], 'Edit');
    }

    /** @test */
    public function can_create_index()
    {
        $this->setPermission('index');

        (new FormWriter($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'class Index extends Controller',
            'public function __invoke(Request $request)',
        ], 'Index');
    }

    /** @test */
    public function can_create_show()
    {
        $this->setPermission('show');

        (new FormWriter($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'use Classes\TestModel;',
            'class Show extends Controller',
            'public function __invoke(TestModel $testModel)',
            'return [\'testModel\' => $testModel]',
        ], 'Show');
    }

    /** @test */
    public function can_create_create()
    {
        $this->setPermission('create');

        (new FormWriter($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'class Create extends Controller',
            'public function __invoke(TestModelForm $form)',
            'return [\'form\' => $form->create()]'
        ], 'Create');
    }

    /** @test */
    public function can_create_destroy()
    {
        $this->setPermission('destroy');

        (new FormWriter($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'use Classes\TestModel;',
            'class Destroy extends Controller',
            'public function __invoke(TestModel $testModel)',
            "'message' => __('The test model was successfully deleted')",
        ], 'Destroy');
    }

    /** @test */
    public function can_create_update()
    {
        $this->setPermission('update');

        (new FormWriter($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'use Classes\TestModel;',
            'use Namespace\App\Http\Requests\Group\ValidateTestModelRequest',
            'class Update extends Controller',
            'public function __invoke(ValidateTestModelRequest $request, TestModel $testModel)',
            "return ['message' => __('The test model was successfully updated')]"
        ], 'Update');
    }

    /** @test */
    public function can_create_store()
    {
        $this->setPermission('store');

        (new FormWriter($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'use Classes\TestModel;',
            'use Namespace\App\Http\Requests\Group\ValidateTestModelRequest',
            'class Store extends Controller',
            '$testModel->fill($request->validated())->save()',
            'public function __invoke(ValidateTestModelRequest $request, TestModel $testModel)',
        ], 'Store');
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'group.testModels'],
            'model' => [
                'name' => 'testModel',
                'namespace' => 'Classes',
            ],
            'permissions' => [],
        ]);
    }

    protected function params()
    {
        return new Obj([
            'root' => $this->root,
            'namespace' => 'Namespace\App\\',
        ]);
    }
}
