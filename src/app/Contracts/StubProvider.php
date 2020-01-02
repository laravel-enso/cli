<?php

namespace LaravelEnso\Cli\App\Contracts;

interface StubProvider
{
    public function prepare(): void;

    public function filePath(): string;

    public function fromTo(): array;

    public function stub(): string;
}
