<?php

namespace LaravelEnso\Cli\App\Services\StubWriters;

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Contracts\Writer as Contract;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Directory;

class Writer implements Contract
{
    private StubProvider $provider;

    public function __construct(StubProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle(): void
    {
        Directory::prepare($this->provider->path());

        File::put($this->filePath(), $this->content());
    }

    public function content()
    {
        $fromTo = $this->provider->fromTo();
        [$from, $to] = [array_keys($fromTo), array_values($fromTo)];

        return str_replace($from, $to, $this->provider->stub());
    }

    private function filePath(): string
    {
        return $this->provider->path().DIRECTORY_SEPARATOR
            .$this->provider->filename();
    }
}
