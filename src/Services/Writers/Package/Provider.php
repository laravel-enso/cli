<?php

namespace LaravelEnso\Cli\Services\Writers\Package;

use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Services\Obj;

class Provider implements StubProvider
{
    private Obj $package;
    private string $namespace;
    private string $rootSegment;
    private string $provider;

    public function __construct(Choices $choices, $provider)
    {
        $this->package = $choices->get('package');
        $this->provider = $provider;
        $this->rootSegment = $choices->params()->get('rootSegment');
        $this->namespace = $choices->params()->get('namespace');
    }

    public function prepare(): void
    {
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        return $this->path($this->provider);
    }

    public function fromTo(): array
    {
        $segments = Collection::wrap(explode('\\', $this->namespace))->slice(0, 2);

        return [
            '${vendor}'    => $this->package->get('vendor'),
            '${package}'   => $this->package->get('name'),
            '${namespace}' => $segments->implode('\\'),
        ];
    }

    public function stub(): string
    {
        return Stub::get($this->provider);
    }

    private function path(?string $filename = null): string
    {
        return Path::get([$this->rootSegment], $filename);
    }
}
