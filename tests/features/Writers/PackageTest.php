<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\Services\Writers\Package;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\Services\Obj;
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
        $this->write(Package::class);

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
        $this->write(Package::class);

        $this->assertCliFileContains('###  enso - cli', 'README.md');
    }

    /** @test */
    public function can_create_licence()
    {
        $this->write(Package::class);

        $this->assertCliFileContains('Copyright (c) '.now()->format('Y').' enso', ['LICENSE']);
    }

    /** @test */
    public function can_create_config()
    {
        $this->choices->get('package')->put('config', true);

        $this->write(Package::class);

        $this->assertFileExists($this->path(['config',  'cli.php']));
    }

    /** @test */
    public function can_create_provider()
    {
        $this->choices->get('package')->put('providers', true);

        $this->write(Package::class);

        $this->assertProvidersContains([
            'namespace Enso\Cli',
            'class AppServiceProvider extends ServiceProvider',
        ], ['AppServiceProvider.php']);

        $this->assertProvidersContains([
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
