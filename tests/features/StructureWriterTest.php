<?php

namespace LaravelEnso\Cli\tests\features;

use Tests\TestCase;
use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Services\Choices;
use LaravelEnso\Cli\app\Services\Structure;

class StructureWriterTest extends TestCase
{
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
        $this->assertTrue($this->formFilesCreated());
        $this->assertTrue($this->tableFilesCreated());
        $this->assertTrue($this->modelCreated());
        $this->assertTrue($this->controllersCreated());
        $this->assertTrue($this->requestValidatorCreated());
        $this->assertTrue($this->pagesCreated());
        $this->assertTrue($this->routesCreated());
        $this->assertTrue($this->migrationsCreated());
    }

    private function modelCreated()
    {
        return File::exists($this->root.'app/Testing/Test.php');
    }

    private function requestValidatorCreated()
    {
        return File::exists($this->root.'app/Http/Requests/Testing/Tests/ValidateTestUpdate.php')
        && File::exists($this->root.'app/Http/Requests/Testing/Tests/ValidateTestStore.php');
    }

    private function formFilesCreated()
    {
        return File::exists($this->root.'app/Forms/Builders/Testing/TestForm.php')
            && File::exists($this->root.'app/Forms/Templates/Testing/test.json');
    }

    private function tableFilesCreated()
    {
        return File::exists($this->root.'app/Tables/Builders/Testing/TestTable.php')
            && File::exists($this->root.'app/Tables/Templates/Testing/tests.json');
    }

    private function controllersCreated()
    {
        return File::exists($this->root.'app/Http/Controllers/Testing/Tests/Index.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/Create.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/Edit.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/Update.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/Store.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/Show.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/Destroy.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/Options.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/InitTable.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/TableData.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Tests/ExportExcel.php');
    }

    private function pagesCreated()
    {
        return File::exists($this->root.'resources/js/pages/testing/tests/Create.vue')
            && File::exists($this->root.'resources/js/pages/testing/tests/Edit.vue')
            && File::exists($this->root.'resources/js/pages/testing/tests/Index.vue')
            && File::exists($this->root.'resources/js/pages/testing/tests/Show.vue');
    }

    private function routesCreated()
    {
        return File::exists($this->root.'resources/js/routes/testing.js')
            && File::exists($this->root.'resources/js/routes/testing/tests.js')
            && File::exists($this->root.'resources/js/routes/testing/tests/create.js')
            && File::exists($this->root.'resources/js/routes/testing/tests/edit.js')
            && File::exists($this->root.'resources/js/routes/testing/tests/index.js')
            && File::exists($this->root.'resources/js/routes/testing/tests/show.js');
    }

    private function migrationsCreated()
    {
        return $this->migrationCreated('create_tests_table')
            && $this->migrationCreated('create_structure_for_tests');
    }

    private function migrationCreated($migration)
    {
        return collect(File::files($this->root.'database/migrations'))
            ->filter(function ($file) use ($migration) {
                return Str::contains($file->getFilename(), $migration);
            })->isNotEmpty();
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

    private function deleteMigration($migration)
    {
        $file = collect(File::files($this->root.'database/migrations'))
            ->filter(function ($file) use ($migration) {
                return Str::contains($file->getFilename(), $migration);
            })->first();

        File::delete($file);
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
