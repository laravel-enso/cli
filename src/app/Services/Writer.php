<?php

namespace LaravelEnso\Cli\App\Services;

use Illuminate\Support\Facades\File;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Directory;

class Writer
{
    private $provider;

    public function __construct(StubProvider $provider)
    {
        $this->provider = $provider;
    }

    public function handle()
    {
        Directory::prepare($this->provider->path());

        File::put($this->provider->filename(), $this->content());

        return $this;
    }

    public function content()
    {
        $fromTo = $this->provider->fromTo();
        [$from, $to] = [array_keys($fromTo), array_values($fromTo)];

        return str_replace($from, $to, $this->provider->stub());
    }
}
