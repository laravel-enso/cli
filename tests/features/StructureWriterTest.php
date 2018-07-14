<?php

use LaravelEnso\StructureManager\app\Classes\StructureWriter;
use LaravelEnso\StructureManager\app\Helpers\TestConfig;
use Tests\TestCase;

class StrucutreWriterTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        // $this->withoutExceptionHandling();
    }

    public function tearDown()
    {
        $this->cleanUp();
    }

    /** @test */
    public function creates_files()
    {
        $config = TestConfig::load();

        (new StructureWriter($config))
            ->run();

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
        return File::exists('app/Http/Controllers/Testing/Projects/Trees/TreeController.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/TreeSelectController.php')
            && File::exists('app/Http/Controllers/Testing/Projects/Trees/TreeTableController.php');
    }

    private function pagesCreated()
    {
        return File::exists('resources/assets/js/pages/testing/projects/trees/Create.vue')
            && File::exists('resources/assets/js/pages/testing/projects/trees/Edit.vue')
            && File::exists('resources/assets/js/pages/testing/projects/trees/Index.vue')
            && File::exists('resources/assets/js/pages/testing/projects/trees/Show.vue');
    }

    private function routesCreated()
    {
        return File::exists('resources/assets/js/routes/testing/projects.js')
            && File::exists('resources/assets/js/routes/testing/projects/trees.js')
            && File::exists('resources/assets/js/routes/testing/projects/trees/create.js')
            && File::exists('resources/assets/js/routes/testing/projects/trees/edit.js')
            && File::exists('resources/assets/js/routes/testing/projects/trees/index.js')
            && File::exists('resources/assets/js/routes/testing/projects/trees/show.js');
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
        \File::deleteDirectory('app/Http/Controllers/Testing/Projects');
        \File::deleteDirectory('app/Http/Requests/Testing');
        \File::deleteDirectory('resources/assets/js/pages/testing/projects');
        \File::deleteDirectory('resources/assets/js/routes/testing/projects');
        \File::delete('resources/assets/js/routes/testing.js');
        \File::delete('resources/assets/js/routes/testing/projects.js');
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
