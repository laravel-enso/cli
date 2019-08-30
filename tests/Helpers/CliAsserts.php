<?php

namespace LaravelEnso\Cli\tests\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

trait CliAsserts
{
    private function segments()
    {
        return collect(explode('.', $this->choices->get('permissionGroup')->get('name')));
    }

    private function ucfirstSegments()
    {
        return $this->segments()->map(function ($perm) {
            return ucfirst($perm);
        });
    }

    private function assertControllerContains($needle, $controller)
    {
        $this->assertContains($needle,
            File::get($this->root.'app/Http/Controllers/'
                .$this->ucfirstSegments()->implode('/')
                .'/'.$controller.'.php')
        );
    }

    private function assertValidatorContains($needle, $validator)
    {
        $this->assertContains($needle,
            File::get($this->root.'app/Http/Requests/'
                .$this->ucfirstSegments()->implode('/')
                .'/'.$validator.'.php')
        );
    }

    private function assertFormBuilderContains($needle)
    {
        $this->assertContains($needle,
            File::get($this->root.'app/Forms/Builders/'
                .$this->ucfirstSegments()->slice(0, -1)->implode('/')
                .'/TestModelForm.php')
        );
    }

    private function assertFileContains($needle, $filePath)
    {
        $this->assertContains($needle,
            File::get($this->root.$filePath));
    }

    private function assertViewRouteFileContains($needle, $filePath)
    {
        $this->assertContains($needle,
            File::get($this->root.'resources/js/routes/'.$filePath));
    }

    private function assertViewPageFileContains($needle, $method)
    {
        $this->assertContains($needle,
            File::get($this->root
                .'resources/js/pages/'
                .$this->segments()->implode('/').'/'
                .$method.'.vue'));
    }

    private function assertStructureMigrationContains($needle)
    {
        $timestamp = Carbon::now()->format('Y_m_d_His');
        $model = Str::snake(Str::plural($this->choices->get('model')->get('name')));
        $this->assertContains($needle,
            File::get($this->root.'database/migrations/'.$timestamp.'_create_structure_for_'.$model.'.php'));
    }

    private function assertTableBuilderContains($needle)
    {
        $this->assertContains($needle,
            File::get($this->root.'app/Tables/Builders/'
                .$this->ucfirstSegments()->slice(0, -1)->implode('/').'/'
                .ucfirst($this->choices->get('model')->get('name'))
                .'Table.php'));
    }

    private function assertTableTemplateContains($needle)
    {
        $this->assertContains($needle,
            File::get($this->root.'app/Tables/Templates/'
                .$this->ucfirstSegments()->slice(0, -1)->implode('/').'/'
                .Str::plural($this->choices->get('model')->get('name'))
                .'.json'));
    }
}
