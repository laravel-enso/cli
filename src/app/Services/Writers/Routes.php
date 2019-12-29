<?php

namespace LaravelEnso\Cli\App\Services\Writers;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\App\Services\BulkWriter;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Cli\App\Services\Writers\Routes\CrudRoutes;
use LaravelEnso\Cli\App\Services\Writers\Routes\SegmentRoutes;
use LaravelEnso\Helpers\App\Classes\Obj;

class Routes
{
    private Choices $choices;
    private Obj $model;
    private Collection $permissions;
    private string $group;
    private ?string $root;

    public function __construct(Choices $choices)
    {
        $this->choices = $choices;
        $this->model = $choices->get('model');
        $this->permissions = $choices->get('permissions')->filter()->keys();
        $this->group = $choices->get('permissionGroup')->get('name');
        $this->root = $choices->params()->get('root');
    }

    public function handle()
    {
        Segments::ucfirst(false);
        Stub::folder('routes');

        (new BulkWriter(new CrudRoutes($this->choices)))->handle();
        (new BulkWriter(new SegmentRoutes($this->choices)))->handle();
    }
}
