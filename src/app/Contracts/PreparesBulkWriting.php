<?php

namespace LaravelEnso\Cli\App\Contracts;

interface PreparesBulkWriting
{
    public function prepare(): void;
}
