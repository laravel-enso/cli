<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\App\Services\Writers\Form;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Tests\Cli;
use Tests\TestCase;

class FormTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp/';

        $this->initChoices();
        Segments::ucfirst();
        Path::segments();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_builder()
    {
        (new Form($this->choices))->handle();

        $this->assertFormBuilderContains([
            'class TestModelForm',
            'public function edit(TestModel $testModel)',
            'return $this->form->edit($testModel);',
        ]);
    }

    /** @test */
    public function can_create_controller()
    {
        $this->setPermission('edit');

        (new Form($this->choices))->handle();

        $this->assertControllerContains([
            'class Edit extends Controller',
            'public function __invoke(TestModel $testModel, TestModelForm $form)',
        ], 'Edit');
    }

    /** @test */
    public function can_create_index()
    {
        $this->setPermission('index');

        (new Form($this->choices))->handle();

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

        (new Form($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'use App\TestModel;',
            'class Show extends Controller',
            'public function __invoke(TestModel $testModel)',
            'return [\'testModel\' => $testModel]',
        ], 'Show');
    }

    /** @test */
    public function can_create_create()
    {
        $this->setPermission('create');

        (new Form($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'class Create extends Controller',
            'public function __invoke(TestModelForm $form)',
            'return [\'form\' => $form->create()]',
        ], 'Create');
    }

    /** @test */
    public function can_create_destroy()
    {
        $this->setPermission('destroy');

        (new Form($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'use App\TestModel;',
            'class Destroy extends Controller',
            'public function __invoke(TestModel $testModel)',
            "'message' => __('The test model was successfully deleted')",
        ], 'Destroy');
    }

    /** @test */
    public function can_create_update()
    {
        $this->setPermission('update');

        (new Form($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'use App\TestModel;',
            'use Namespace\App\Http\Requests\Group\ValidateTestModelRequest',
            'class Update extends Controller',
            'public function __invoke(ValidateTestModelRequest $request, TestModel $testModel)',
            "return ['message' => __('The test model was successfully updated')]",
        ], 'Update');
    }

    /** @test */
    public function can_create_store()
    {
        $this->setPermission('store');

        (new Form($this->choices))->handle();

        $this->assertControllerContains([
            'namespace Namespace\App\Http\Controllers\Group\TestModels;',
            'use App\TestModel;',
            'use Namespace\App\Http\Requests\Group\ValidateTestModelRequest',
            'class Store extends Controller',
            '$testModel->fill($request->validated())->save()',
            'public function __invoke(ValidateTestModelRequest $request, TestModel $testModel)',
        ], 'Store');
    }
}
