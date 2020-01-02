<?php

namespace LaravelEnso\Cli\App\Contracts;

use Illuminate\Support\Collection;

interface BulkProvider
{
    public function collection(): Collection;
}
