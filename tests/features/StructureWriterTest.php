<?php

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Structure;
use LaravelEnso\Cli\Tests\Cli;
use LaravelEnso\Helpers\Classes\Obj;
use Tests\TestCase;

class StructureWriterTest extends TestCase
{
    use Cli;

    private $choices;
    private $params;
    private $root;
    private $config;

    public function setUp(): void
    {
        parent::setUp();

        $this->params = $this->params();
        $this->choices = $this->choices();
    }

    public function tearDown(): void
    {
        $this->cleanUp();

        parent::tearDown();
    }

    /** @test */
    public function can_generate_local_structure()
    {
        $this->root = '';

        $this->handle();

        $this->makeAssertions();
    }

    /** @test */
    public function can_generate_package()
    {
        $this->root = 'vendor/laravel-enso/testing/src';
        $this->rootSegment = 'App';

        $this->choices->set('package', new Obj([
            'name' => 'testing',
            'vendor' => 'laravel-enso',
            'providers' => true,
            'config' => true,
        ]));

        $this->choices->get('model')->set('name', 'Testing/PackageTest');
        $this->choices->get('permissionGroup')->set('name', 'testing.packageTests');
        $this->choices->get('menu')->set('route', 'testing.packageTests.index');

        $this->handle();

        $this->makeAssertions();
    }

    private function makeAssertions()
    {
        $this->formFilesCreated();
        $this->tableFilesCreated();
        $this->modelCreated();
        $this->controllersCreated();
        $this->requestValidatorCreated();
        $this->pagesCreated();
        $this->routesCreated();
        $this->migrationsCreated();
    }

    private function modelCreated()
    {
        $this->assertFileExists($this->path([$this->rootSegment, 'Testing', "{$this->modelName()}.php"]));
    }

    private function requestValidatorCreated()
    {
        $this->assertValidatorExists("Validate{$this->modelName()}Request");
    }

    private function formFilesCreated()
    {
        $this->assertFormBuilderExists();
        $this->assertFormTemplateExists();
    }

    private function tableFilesCreated()
    {
        $this->assertTableTemplateExists();
        $this->assertTableBuilderExists();
    }

    private function controllersCreated()
    {
        $this->controllers()->each(fn ($controller) => (
            $this->assertControllerExists($controller)
        ));
    }

    private function pagesCreated()
    {
        (new Collection(['Create', 'Edit', 'Index', 'Show']))
            ->each(fn ($view) => $this->assertViewPageExists($view));
    }

    private function routesCreated()
    {
        $permission = Str::camel($this->tableName());

        (new Collection([
            'testing.js', "testing/{$permission}.js", "testing/{$permission}/create.js",
            "testing/{$permission}/edit.js", "testing/{$permission}/index.js",
            "testing/{$permission}/show.js",
        ]))->each(fn ($route) => $this->assertViewRouteExists($route));

        return true;
    }

    private function migrationsCreated()
    {
        $this->assertMigrationCreated("create_{$this->tableName()}_table");
        $this->assertMigrationCreated("create_structure_for_{$this->tableName()}");
    }

    private function cleanUp()
    {
        if ($this->root) {
            File::deleteDirectory(str_replace('src/', '', $this->root));

            return;
        }

        File::deleteDirectory($this->path([$this->rootSegment, 'Testing']));
        File::deleteDirectory($this->path([$this->rootSegment, 'Forms', 'Builders', 'Testing']));
        File::deleteDirectory($this->path([$this->rootSegment, 'Forms', 'Templates', 'Testing']));
        File::deleteDirectory($this->path([$this->rootSegment, 'Tables', 'Builders', 'Testing']));
        File::deleteDirectory($this->path([$this->rootSegment, 'Tables', 'Templates', 'Testing']));
        File::deleteDirectory($this->path([$this->rootSegment, 'Http', 'Controllers', 'Testing']));
        File::deleteDirectory($this->path([$this->rootSegment, 'Http', 'Requests', 'Testing']));
        File::deleteDirectory($this->path(['client', 'src', 'js', 'pages', 'testing']));
        File::deleteDirectory($this->path(['client', 'src', 'js', 'routes', 'testing']));
        File::delete($this->path(['client', 'src', 'js', 'routes', 'testing.js']));
        $this->deleteMigration("create_{$this->tableName()}_table");
        $this->deleteMigration("create_structure_for_{$this->tableName()}");
    }

    private function handle()
    {
        $this->config = (new Choices(new Command()))
            ->setChoices($this->choices)
            ->setParams($this->params);

        (new Structure($this->config))->handle();
    }

    private function choices()
    {
        return new Obj(json_decode(
            File::get(__DIR__.'/stubs/testConfiguration.stub')
        ));
    }

    private function params()
    {
        return new Obj(json_decode(
            File::get(__DIR__.'/stubs/testParams.stub')
        ));
    }
}
