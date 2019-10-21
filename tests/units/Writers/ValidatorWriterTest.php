<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\tests\Helpers\Cli;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Writers\ValidatorWriter;

class ValidatorWriterTest extends TestCase
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
    public function can_create_request()
    {
        $this->setPermission('store');

        (new ValidatorWriter($this->choices))->handle();

        $this->assertValidatorContains([
            'namespace Namespace\\App\\Http\\Requests\\Perm;',
            'class ValidateTestModelRequest extends FormRequest',
        ], 'ValidateTestModelRequest');
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'perm.group'],
            'model' => ['name' => 'testModel'],
        ]);
    }

    protected function params()
    {
        return new Obj([
            'root' => $this->root, 'namespace' => 'Namespace\\App\\',
        ]);
    }
}
