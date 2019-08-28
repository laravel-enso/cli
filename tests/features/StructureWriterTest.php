<?php

use Tests\TestCase;
use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\app\Helpers\TestConfig;
use LaravelEnso\Cli\app\Services\Structure;

class StructureWriterTest extends TestCase
{
    private $choices;
    private $params;
    private $root;

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
    public function can_generate_local_structure()
    {
        $this->params = TestConfig::loadParams();
        $this->choices = TestConfig::loadStructure();

        $this->generateFiles();
    }

    /** @test */
    public function can_generate_package()
    {
        $this->params = TestConfig::loadParams();
        $this->choices = TestConfig::loadPackageStructure();

        $this->generateFiles();
    }

    private function generateFiles()
    {
        (new Structure($this->choices, $this->params))->handle();

        $this->root = $this->params->get('root');

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
        return File::exists($this->params->get('root').'app/Test/Tree.php');
    }

    private function requestValidatorCreated()
    {
        return File::exists($this->root.'app/Http/Requests/ValidateTreeUpdate.php')
        && File::exists($this->root.'app/Http/Requests/ValidateTreeStore.php');
    }

    private function formFilesCreated()
    {
        return File::exists($this->root.'app/Forms/Builders/Testing/Projects/TreeForm.php')
            && File::exists($this->root.'app/Forms/Templates/Testing/Projects/tree.json');
    }

    private function tableFilesCreated()
    {
        return File::exists($this->root.'app/Tables/Builders/Testing/Projects/TreeTable.php')
            && File::exists($this->root.'app/Tables/Templates/Testing/Projects/trees.json');
    }

    private function controllersCreated()
    {
        return File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/Index.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/Create.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/Edit.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/Update.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/Store.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/Show.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/Destroy.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/Options.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/InitTable.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/TableData.php')
            && File::exists($this->root.'app/Http/Controllers/Testing/Projects/Trees/ExportExcel.php');
    }

    private function pagesCreated()
    {
        return File::exists($this->root.'resources/js/pages/testing/projects/trees/Create.vue')
            && File::exists($this->root.'resources/js/pages/testing/projects/trees/Edit.vue')
            && File::exists($this->root.'resources/js/pages/testing/projects/trees/Index.vue')
            && File::exists($this->root.'resources/js/pages/testing/projects/trees/Show.vue');
    }

    private function routesCreated()
    {
        return File::exists($this->root.'resources/js/routes/testing/projects.js')
            && File::exists($this->root.'resources/js/routes/testing/projects/trees.js')
            && File::exists($this->root.'resources/js/routes/testing/projects/trees/create.js')
            && File::exists($this->root.'resources/js/routes/testing/projects/trees/edit.js')
            && File::exists($this->root.'resources/js/routes/testing/projects/trees/index.js')
            && File::exists($this->root.'resources/js/routes/testing/projects/trees/show.js');
    }

    private function migrationsCreated()
    {
        return $this->migrationCreated('create_trees_table')
            && $this->migrationCreated('create_structure_for_trees');
    }

    private function migrationCreated($migration)
    {
        return collect(File::files($this->root.'database/migrations'))
            ->filter(function ($file) use ($migration) {
                return strpos($file->getFilename(), $migration) > 0;
            })->isNotEmpty();
    }

    private function cleanUp()
    {
        if (!empty($this->root)) {
            File::deleteDirectory($this->root);
            return;
        }
        File::delete($this->root.'app/Test/Tree.php');
        File::deleteDirectory($this->root.'app/Forms/Builders/Testing');
        File::deleteDirectory($this->root.'app/Forms/Templates/Testing');
        File::deleteDirectory($this->root.'app/Tables/Builders/Testing');
        File::deleteDirectory($this->root.'app/Tables/Templates/Testing');
        File::deleteDirectory($this->root.'app/Http/Controllers/Testing');
        File::deleteDirectory($this->root.'resources/js/pages/testing');
        File::deleteDirectory($this->root.'resources/js/routes/testing');
        File::delete($this->root.'app/Http/Requests/ValidateTreeStore.php');
        File::delete($this->root.'app/Http/Requests/ValidateTreeUpdate.php');
        File::delete($this->root.'resources/js/routes/testing.js');
        $this->deleteMigration('create_trees_table');
        $this->deleteMigration('create_structure_for_trees');

        shell_exec('composer dump-autoload');
    }

    private function deleteMigration($migration)
    {
        $file = collect(File::files($this->root.'database/migrations'))
            ->filter(function ($file) use ($migration) {
                return strpos($file->getFilename(), $migration) > 0;
            })->first();

        File::delete($file);
    }
}
