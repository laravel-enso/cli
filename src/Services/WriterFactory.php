<?php

namespace LaravelEnso\Cli\Services;

use LaravelEnso\Cli\Contracts\BulkProvider;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Exceptions\WriterProvider;
use LaravelEnso\Cli\Services\StubWriters\BulkWriter;
use LaravelEnso\Cli\Services\StubWriters\Writer;

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
