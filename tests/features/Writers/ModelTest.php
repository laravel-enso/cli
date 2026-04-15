<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\Services\StubWriters\Writer;
use LaravelEnso\Cli\Services\Writers\Model;
use LaravelEnso\Cli\Tests\Cli;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ModelTest extends TestCase
{
    use Cli;

    private $root;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp';

        $this->initChoices();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    #[Test]
    public function can_create_model_with_path()
    {
        $this->choices->get('model')->put('path', 'app');

        (new Writer(new Model($this->choices)))->handle();

        $this->assertFileExists("{$this->root}/app/TestModel.php");
    }

    #[Test]
    public function can_create_model()
    {
        (new Writer(new Model($this->choices)))->handle();

        $this->assertCliFileContains('class TestModel extends Model', ['TestModel.php']);
    }
}
