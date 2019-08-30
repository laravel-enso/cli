<?php

namespace LaravelEnso\Cli\tests\units\Writers;

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\tests\Helpers\CliAsserts;
use LaravelEnso\Cli\app\Writers\PackageWriter;

class PackageWriterTest extends TestCase
{
    use CliAsserts;

    private $root;
    private $params;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = 'cli_tests_tmp/';

        $this->choices = new Obj([
            'package' => [
                'vendor' => 'enso',
                'name' => 'cli',
            ],
        ]);

        $this->params = new Obj([
            'root' => $this->root,
            'namespace' => 'Enso\Cli\app\\',
        ]);
    }

    protected function tearDown() :void
    {
        parent::tearDown();

        File::deleteDirectory($this->root);
    }

    /** @test */
    public function can_create_directory()
    {
        (new PackageWriter($this->choices, $this->params))->run();

        $this->assertDirectoryExists($this->root);
    }

    /** @test */
    public function can_create_composer()
    {
        (new PackageWriter($this->choices, $this->params))->run();

        $this->assertFileContains('"name": "enso/cli"', 'composer.json');
        $this->assertFileContains('"Enso\\\\Cli\\\\": "src/"', 'composer.json');
        $this->assertFileContains('"Enso\\\\Cli\\\\AppServiceProvider"', 'composer.json');
        $this->assertFileContains('"Enso\\\\Cli\\\\AuthServiceProvider"', 'composer.json');
    }

    /** @test */
    public function can_create_readme()
    {
        (new PackageWriter($this->choices, $this->params))->run();

        $this->assertFileContains('###  enso - cli', 'README.md');
    }

    /** @test */
    public function can_create_licence()
    {
        (new PackageWriter($this->choices, $this->params))->run();

        $this->assertFileContains('Copyright (c) '.now()->format('Y').' enso', 'LICENSE');
    }

    /** @test */
    public function can_create_config()
    {
        $this->choices->get('package')->put('config', true);

        (new PackageWriter($this->choices, $this->params))->run();

        $this->assertFileExists($this->root.'config/cli.php');
    }

    /** @test */
    public function can_create_provider()
    {
        $this->choices->get('package')->put('providers', true);

        (new PackageWriter($this->choices, $this->params))->run();

        $namespace = 'namespace Enso\Cli';
        $this->assertFileContains($namespace, 'AppServiceProvider.php');
        $this->assertFileContains('class AppServiceProvider extends ServiceProvider', 'AppServiceProvider.php');
        $this->assertFileContains($namespace, 'AuthServiceProvider.php');
        $this->assertFileContains('class AuthServiceProvider extends ServiceProvider', 'AuthServiceProvider.php');
    }
}
