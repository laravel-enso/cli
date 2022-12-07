<?php

namespace LaravelEnso\Cli\Services\Writers\Form;

use Illuminate\Support\Str;
use LaravelEnso\Cli\Services\Writers\Helpers\Controller as BaseController;
use LaravelEnso\Cli\Services\Writers\Helpers\Namespacer;

class Controller extends BaseController
{
    public function fromTo(): array
    {
        return [
            '${Model}'            => $this->model->get('name'),
            '${model}'            => lcfirst($this->model->get('name')),
            '${title}'            => Str::snake($this->model->get('name'), ' '),
            '${permissionGroup}'  => $this->group,
            '${namespace}'        => Namespacer::get(['Http', 'Controllers'], true),
            '${modelNamespace}'   => $this->model->get('namespace'),
            '${builderNamespace}' => Namespacer::get(['Forms', 'Builders']),
            '${requestNamespace}' => Namespacer::get(['Http', 'Requests']),
            '${request}'          => "Validate{$this->model->get('name')}",
        ];
    }
}
