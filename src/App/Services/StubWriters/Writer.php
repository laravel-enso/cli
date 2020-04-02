<?php

namespace LaravelEnso\Cli\App\Services\StubWriters;

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Contracts\Writer as Contract;

class Writer implements Contract
{
    private StubProvider $provider;

    public function __construct(StubProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(): void
    {
        $this->provider->prepare();

        File::put($this->provider->filePath(), $this->content());
    }

    private function content(): string
    {
        $fromTo = $this->provider->fromTo();
        [$from, $to] = [array_keys($fromTo), array_values($fromTo)];

        return str_replace($from, $to, $this->provider->stub());
    }
}
