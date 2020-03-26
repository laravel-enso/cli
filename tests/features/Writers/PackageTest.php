<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\App\Services\Writers\Package;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\App\Classes\Obj;
use Tests\TestCase;

class PackageTest extends TestCase
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

    /** @test */
    public function can_create_composer()
    {
        (new Package($this->choices))->handle();

        $this->assertCliFileContains([
            '"name": "enso/cli"',
            '"Enso\\\\Cli\\\\": "src/"',
            '"Enso\\\\Cli\\\\AppServiceProvider"',
            '"Enso\\\\Cli\\\\AuthServiceProvider"',
        ], ['composer.json']);
    }

    /** @test */
    public function can_create_readme()
    {
        (new Package($this->choices))->handle();

        $this->assertCliFileContains('###  enso - cli', 'README.md');
    }

    /** @test */
    public function can_create_licence()
    {
        (new Package($this->choices))->handle();

        $this->assertCliFileContains('Copyright (c) '.now()->format('Y').' enso', ['LICENSE']);
    }

    /** @test */
    public function can_create_config()
    {
        $this->choices->get('package')->put('config', true);

        (new Package($this->choices))->handle();

        $this->assertFileExists($this->path(['config',  'cli.php']));
    }

    /** @test */
    public function can_create_provider()
    {
        $this->choices->get('package')->put('providers', true);

        (new Package($this->choices))->handle();

        $this->assertCliFileContains([
            'namespace Enso\Cli',
            'class AppServiceProvider extends ServiceProvider',
        ], ['AppServiceProvider.php']);

        $this->assertCliFileContains([
            'namespace Enso\Cli',
            'class AuthServiceProvider extends ServiceProvider',
        ], ['AuthServiceProvider.php']);
    }

    protected function choices()
    {
        return new Obj([
            'package' => [
                'vendor' => 'enso',
                'name' => 'cli',
            ],
        ]);
    }

    protected function params()
    {
        return new Obj([
            'root' => $this->root,
            'namespace' => 'Enso\Cli\App',
            'rootSegment' => 'app'
        ]);
    }
}
