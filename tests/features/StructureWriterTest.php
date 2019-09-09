<?php

namespace LaravelEnso\Cli\tests\features;

use Tests\TestCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Services\Choices;
use LaravelEnso\Cli\app\Services\Structure;
use LaravelEnso\Cli\tests\Helpers\Cli;

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
        $this->root = $this->params->get('root');
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
        $this->handle();

        $this->makeAssertions();
    }

    /** @test */
    public function can_generate_package()
    {
        $this->choices->set('package', new Obj([
            'name' => 'testing',
            'vendor' => 'laravel-enso',
            'providers' => true,
            'config' => true
        ]));

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
        $this->assertFileExists($this->root.'app/Testing/Test.php');
    }

    private function requestValidatorCreated()
    {
        $this->assertValidatorExists('ValidateTestUpdate');
        $this->assertValidatorExists('ValidateTestStore');
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
        $this->controllers()->each(function ($controller) {
            $this->assertControllerExists($controller);
        });
    }

    private function pagesCreated()
    {
        collect(['Create', 'Edit', 'Index', 'Show'])->each(function ($view) {
            $this->assertViewPageExists($view);
        });
    }

    private function routesCreated()
    {
        collect(['testing.js', 'testing/tests.js', 'testing/tests/create.js',
            'testing/tests/edit.js', 'testing/tests/index.js', 'testing/tests/show.js',])
            ->each(function ($route) {
                $this->assertViewRouteExists($route);
            });

        return true;
    }

    private function migrationsCreated()
    {
        $this->assertMigrationCreated('create_tests_table');
        $this->assertMigrationCreated('create_structure_for_tests');
    }

    private function cleanUp()
    {
        if (! empty($this->root)) {
            File::deleteDirectory($this->root);

            return;
        }
        File::delete($this->root.'app/Testing/Test.php');
        File::deleteDirectory($this->root.'app/Forms/Builders/Testing');
        File::deleteDirectory($this->root.'app/Forms/Templates/Testing');
        File::deleteDirectory($this->root.'app/Tables/Builders/Testing');
        File::deleteDirectory($this->root.'app/Tables/Templates/Testing');
        File::deleteDirectory($this->root.'app/Http/Controllers/Testing');
        File::deleteDirectory($this->root.'app/Http/Requests/Testing');
        File::deleteDirectory($this->root.'resources/js/pages/testing');
        File::deleteDirectory($this->root.'resources/js/routes/testing');
        File::delete($this->root.'resources/js/routes/testing.js');
        $this->deleteMigration('create_tests_table');
        $this->deleteMigration('create_structure_for_tests');
    }

    private function handle()
    {
        $this->config = (new Choices(new Command))
            ->setChoices($this->choices())
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
