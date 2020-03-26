<?php

namespace LaravelEnso\Cli\App\Services\StubWriters;

use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Contracts\PreparesBulkWriting;
use LaravelEnso\Cli\App\Contracts\Writer as Contract;
use LaravelEnso\Cli\App\Services\WriterFactory;

class BulkWriter implements Contract
{
    private BulkProvider $bulkProvider;

    public function __construct(BulkProvider $bulkProvider)
    {
        $this->bulkProvider = $bulkProvider;
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
