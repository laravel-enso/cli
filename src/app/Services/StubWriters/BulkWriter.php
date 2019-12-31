<?php

namespace LaravelEnso\Cli\App\Services\StubWriters;

use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Contracts\Writer as Factory;
use LaravelEnso\Cli\App\Services\WriterFactory;

class BulkWriter implements Factory
{
    private BulkProvider $provider;

    public function __construct(BulkProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(): void
    {
        $this->provider->collection()
            ->each(fn ($provider) => WriterFactory::writer($provider)->handle());
    }
}
