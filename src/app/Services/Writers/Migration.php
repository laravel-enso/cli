<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Directory;
use LaravelEnso\Helpers\App\Classes\Obj;

class Migration
{
    private Obj $model;
    private ?string $root;

    public function __construct(Choices $choices)
    {
        $this->model = $choices->get('model');
        $this->root = $choices->params()->get('root');
    }

    public function handle()
    {
        $path = $this->path();

        Directory::prepare($path);

        $name = Str::plural(Str::snake($this->model->get('name')));

        Artisan::call('make:migration', [
            'name' => "create_{$name}_table",
            '--path' => $path,
        ]);
    }

    private function path()
    {
        return (new Collection([$this->root, 'database', 'migrations']))
            ->filter()->implode(DIRECTORY_SEPARATOR);
    }
}
