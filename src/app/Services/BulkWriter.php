<?php

namespace LaravelEnso\Cli\App\Services;

use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Contracts\StubProvider;

class BulkWriter
{
    private $provider;

    public function __construct(BulkProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle()
    {
        $this->provider->collection()
            ->each(fn (StubProvider $provider) => (new Writer($provider))->handle());
    }
}
