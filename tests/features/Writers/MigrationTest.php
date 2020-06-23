<?php

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\Services\Writers\Migration;
use LaravelEnso\Cli\Tests\Cli;
use Tests\TestCase;

class MigrationTest extends TestCase
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
    public function can_create_migration()
    {
        $this->choices->get('files')->put('table', true);

        (new Migration($this->choices))->handle();

        $this->assertMigrationCreated('create_test_models_table');
    }
}
