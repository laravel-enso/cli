<?php

use Tests\TestCase;
use LaravelEnso\Cli\app\Helpers\TestConfig;
use LaravelEnso\Cli\app\Services\Structure;

class StructureWriterTest extends TestCase
{
    protected function setUp(): void
    {
        // $this->withoutExceptionHandling();

        parent::setUp();
    }

    public function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    /** @test */
    public function can_generate_files()
    {
        $config = TestConfig::load();

        (new Structure($config))->handle();

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
        return File::exists('app/Tree.php');
    }

    private function requestValidatorCreated()
    {
        return File::exists('app/Http/Requests/Testing/Projects/ValidateTreeRequest.php');
    }

    private function formFilesCreated()
    {
        return File::exists('app/Forms/Builders/Testing/Projects/TreeForm.php')
            && File::exists('app/Forms/Templates/Testing/Projects/tree.json');
    }

    private function tableFilesCreated()
    {
        return File::exists('app/Tables/Builders/Testing/Projects/TreeTable.php')
            && File::exists('app/Tables/Templates/Testing/Projects/trees.json');
    }

    private function controllersCreated()
    {
        return File::exists('app/Http/Controllers/Testing/Projects/Trees/Index.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/Create.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/Edit.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/Update.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/Store.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/Show.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/Destroy.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/Options.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/InitTable.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/TableData.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/ExportExcel.php');
    }

    private function pagesCreated()
    {
        return File::exists('resources/js/pages/testing/projects/trees/Create.vue')
            && File::exists('resources/js/pages/testing/projects/trees/Edit.vue')
            && File::exists('resources/js/pages/testing/projects/trees/Index.vue')
            && File::exists('resources/js/pages/testing/projects/trees/Show.vue');
    }

    private function routesCreated()
    {
        return File::exists('resources/js/routes/testing/projects.js')
            && File::exists('resources/js/routes/testing/projects/trees.js')
            && File::exists('resources/js/routes/testing/projects/trees/create.js')
            && File::exists('resources/js/routes/testing/projects/trees/edit.js')
            && File::exists('resources/js/routes/testing/projects/trees/index.js')
            && File::exists('resources/js/routes/testing/projects/trees/show.js');
    }

    private function migrationsCreated()
    {
        return $this->migrationCreated('create_trees_table')
            && $this->migrationCreated('create_structure_for_trees');
    }

    private function migrationCreated($migration)
    {
        return collect(File::files(database_path('migrations')))
            ->filter(function ($file) use ($migration) {
                return strpos($file->getFilename(), $migration) > 0;
            })->isNotEmpty();
    }

    private function cleanUp()
    {
        \File::delete('app/Tree.php');
        \File::deleteDirectory('app/Forms/Builders/Testing');
        \File::deleteDirectory('app/Forms/Templates/Testing');
        \File::deleteDirectory('app/Tables/Builders/Testing');
        \File::deleteDirectory('app/Tables/Templates/Testing');
        \File::deleteDirectory('app/Http/Controllers/Testing');
        \File::deleteDirectory('app/Http/Requests/Testing');
        \File::deleteDirectory('resources/js/pages/testing');
        \File::deleteDirectory('resources/js/routes/testing');
        \File::delete('resources/js/routes/testing.js');
        $this->deleteMigration('create_trees_table');
        $this->deleteMigration('create_structure_for_trees');

        shell_exec('composer dump-autoload');
    }

    private function deleteMigration($migration)
    {
        $file = collect(File::files(database_path('migrations')))
            ->filter(function ($file) use ($migration) {
                return strpos($file->getFilename(), $migration) > 0;
            })->first();

        \File::delete($file);
    }
}
