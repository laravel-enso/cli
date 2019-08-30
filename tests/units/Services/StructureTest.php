<?php

namespace LaravelEnso\Cli\tests\units\Services;

use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Cli\app\Services\Structure;
use LaravelEnso\Cli\app\Writers\FormWriter;
use LaravelEnso\Cli\app\Writers\TableWriter;
use LaravelEnso\Cli\app\Writers\ViewsWriter;
use LaravelEnso\Cli\app\Writers\RoutesWriter;
use LaravelEnso\Cli\app\Writers\OptionsWriter;
use LaravelEnso\Cli\app\Writers\PackageWriter;
use LaravelEnso\Cli\app\Writers\ValidatorWriter;
use LaravelEnso\Cli\app\Writers\ModelAndMigrationWriter;
use LaravelEnso\Cli\app\Writers\StructureMigrationWriter;

class StructureTest extends TestCase
{
    private $spy;
    private $params;
    private $choices;

    protected function setUp(): void
    {
        parent::setUp();

        $this->choices = new Obj([
            'model' => [],
        ]);

        $this->params = new Obj([]);

        $this->initSpies();
    }

    /** @test */
    public function can_set_namespace_in_model_without_namespace()
    {
        $this->choices->put('files', new Obj(['model' => true]));
        $this->choices->put('model', new Obj(['name' => 'model']));

        (new Structure($this->choices, $this->params))->handle();

        $args = $this->spy->get(ModelAndMigrationWriter::class);
        $this->assertEquals($args['choices']->get('model')->get('namespace'), 'App');
        $this->assertEquals($args['choices']->get('model')->get('name'), 'model');
    }

    /** @test */
    public function can_set_namespace_and_path_in_model_with_namespace()
    {
        $this->choices->put('files', new Obj(['model' => true]));
        $this->choices->put('model', new Obj(['name' => 'namespace/model']));

        (new Structure($this->choices, $this->params))->handle();

        $choices = $this->spy->get(ModelAndMigrationWriter::class)['choices'];
        $this->assertEquals($choices->get('model')->get('namespace'), 'App\namespace');
        $this->assertEquals($choices->get('model')->get('name'), 'model');
        $this->assertEquals($choices->get('model')->get('path'), 'namespace');
    }

    /** @test */
    public function can_set_namespace_of_model_with_package()
    {
        $this->choices->put('files', new Obj(['model' => true]));
        $this->choices->put('model', new Obj(['name' => 'namespace/model']));
        $this->choices->put('package', new Obj(['name' => 'package', 'vendor' => 'user']));

        (new Structure($this->choices, $this->params))->handle();

        $choices = $this->spy->get(ModelAndMigrationWriter::class)['choices'];
        $this->assertEquals($choices->get('model')->get('namespace'), 'User\Package\app\namespace');
    }

    /** @test */
    public function can_set_root_and_namespace_in_packages()
    {
        $this->choices->put('package', new Obj(['name' => 'package', 'vendor' => 'user']));

        (new Structure($this->choices, $this->params))->handle();

        $params = $this->spy->get(PackageWriter::class)['params'];
        $choices = $this->spy->get(PackageWriter::class)['choices'];
        $this->assertEquals($params->get('root'), 'vendor/user/package/src/');
        $this->assertEquals($params->get('namespace'), 'User\Package\app\\');
    }

    /** @test */
    public function can_call_all_writers()
    {
        $this->choices->put('files', new Obj([
            'form' => true, 'model' => true, 'table' => true, 'options' => true,
            'routes' => true, 'views' => true,
        ]));

        (new Structure($this->choices, $this->params))->handle();

        $result = collect([
            TableWriter::class, ViewsWriter::class, RoutesWriter::class, OptionsWriter::class,
            ValidatorWriter::class, ModelAndMigrationWriter::class, StructureMigrationWriter::class, FormWriter::class,
        ])->each(function ($writer) {
            $this->assertEquals($this->spy->get($writer)['choices'], $this->choices, $writer.' called with wrong choices');
            $this->assertEquals($this->spy->get($writer)['params'], $this->params, $writer.' called with wrong params');
        });
    }

    /** @test */
    public function call_writer_only_when_related_file_exist()
    {
        $filesToWriters = collect([
            'model' => [ModelAndMigrationWriter::class],
            'table' => [TableWriter::class],
            'options' => [OptionsWriter::class],
            'routes' => [RoutesWriter::class],
            'table migration' => [ModelAndMigrationWriter::class],
        ]);

        $filesToWriters->each(function ($writers, $file) {
            $this->spy = new Obj();

            $this->choices->put('files', new Obj([$file => true]));

            (new Structure($this->choices, $this->params))->handle();

            $result = collect([
                TableWriter::class, ViewsWriter::class, RoutesWriter::class, OptionsWriter::class,
                ValidatorWriter::class, ModelAndMigrationWriter::class, FormWriter::class,
            ])->each(function ($writer) use ($file, $writers) {
                if (collect($writers)->contains($writer)) {
                    $this->assertTrue($this->spy->has($writer),
                        'when '.$file.' is enable then '.$writer.' should be called');
                } else {
                    $this->assertFalse($this->spy->has($writer),
                        'when only '.$file.' is enable then '.$writer.' should not called');
                }
            });
        });
    }

    protected function initSpies(): void
    {
        $this->spy = new Obj();

        collect([
            TableWriter::class, ViewsWriter::class, RoutesWriter::class, OptionsWriter::class, PackageWriter::class,
            ValidatorWriter::class, ModelAndMigrationWriter::class, StructureMigrationWriter::class, FormWriter::class,
        ])->each(function ($writer) {
            $this->app->bind($writer, function ($choices, $args) use ($writer) {
                $this->spy->put($writer, $args);

                return new DummyWriter();
            });
        });
    }
}

class DummyWriter
{
    public function run()
    {
    }
}
