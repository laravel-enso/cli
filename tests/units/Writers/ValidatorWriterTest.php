<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\tests\Helpers\CliAsserts;
use LaravelEnso\Cli\app\Writers\ValidatorWriter;

class ValidatorWriterTest extends TestCase
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
            'namespace' => 'Namespace\\App\\',
        ]);
    }

    protected function tearDown() :void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_store()
    {
        $this->choices->put('permissions', new Obj(['store' => 'store']));
        (new ValidatorWriter($this->choices, $this->params))->run();

        $this->assertValidatorContains('namespace Namespace\\App\\Http\\Requests\\Group\\TestModels;', 'ValidateTestModelStore');
        $this->assertValidatorContains('class ValidateTestModelStore extends FormRequest', 'ValidateTestModelStore');
    }

    /** @test */
    public function can_create_update()
    {
        $this->choices->put('permissions', new Obj(['update' => 'update']));
        (new ValidatorWriter($this->choices, $this->params))->run();

        $this->assertValidatorContains('namespace Namespace\\App\\Http\\Requests\\Group\\TestModels;', 'ValidateTestModelUpdate');
        $this->assertValidatorContains('class ValidateTestModelUpdate extends ValidateTestModelStore', 'ValidateTestModelUpdate');
    }
}
