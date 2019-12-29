<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\App\Services\Writer;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Validator;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\App\Classes\Obj;
use Tests\TestCase;

class RequestValidatorTest extends TestCase
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
    public function can_create_request()
    {
        $this->setPermission('store');

        (new Writer(new Validator($this->choices)))->handle();

        $this->assertValidatorContains([
            'namespace Namespace\\App\\Http\\Requests\\Perm;',
            'class ValidateTestModelRequest extends FormRequest',
        ], 'ValidateTestModelRequest');
    }

    protected function choices()
    {
        return new Obj([
            'permissionGroup' => ['name' => 'perm.group'],
            'model' => ['name' => 'TestModel'],
        ]);
    }

    protected function params()
    {
        return new Obj([
            'root' => $this->root, 'namespace' => 'Namespace\\App',
        ]);
    }
}
