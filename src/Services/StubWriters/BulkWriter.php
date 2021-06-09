<?php

namespace LaravelEnso\Cli\Services\StubWriters;

use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Contracts\PreparesBulkWriting;
use LaravelEnso\Cli\Contracts\Writer as Contract;
use LaravelEnso\Cli\Services\WriterFactory;

class BulkWriter implements Contract
{
    public function __construct(private BulkProvider $bulkProvider)
    {
    }

    public function handle(): void
    {
        if ($this->bulkProvider instanceof PreparesBulkWriting) {
            $this->bulkProvider->prepare();
        }

        $this->bulkProvider->collection()
            ->each(fn ($provider) => WriterFactory::make($provider)->handle());
    }
}
