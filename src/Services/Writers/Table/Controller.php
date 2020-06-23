<?php

namespace LaravelEnso\Cli\Services\Writers\Table;

use LaravelEnso\Cli\Services\Writers\Helpers\Controller as BaseController;
use LaravelEnso\Cli\Services\Writers\Helpers\Namespacer;

class Controller extends BaseController
{
    public function fromTo(): array
    {
        return [
            '${namespace}' => Namespacer::get(['Http', 'Controllers'], true),
            '${builderNamespace}' => Namespacer::get(['Tables', 'Builders']),
            '${Model}' => $this->model->get('name'),
        ];
    }
}
