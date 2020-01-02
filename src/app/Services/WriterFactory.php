<?php

namespace LaravelEnso\Cli\App\Services;

use LaravelEnso\Cli\App\Contracts\BulkProvider;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Exceptions\WriterProvider;
use LaravelEnso\Cli\App\Services\StubWriters\BulkWriter;
use LaravelEnso\Cli\App\Services\StubWriters\Writer;

class WriterFactory
{
    public static function make(object $provider)
    {
        if ($provider instanceof StubProvider) {
            return new Writer($provider);
        }

        if ($provider instanceof BulkProvider) {
            return new BulkWriter($provider);
        }

        throw WriterProvider::unknown($provider);
    }
}
