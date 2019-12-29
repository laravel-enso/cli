<?php

namespace LaravelEnso\Cli\App\Contracts;

interface StubProvider
{
    public function path(): string;

    public function filename(): string;

    public function fromTo(): array;

    public function stub(): string;
}
