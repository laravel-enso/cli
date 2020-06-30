<?php

namespace LaravelEnso\Cli\Services\Writers\Package;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\Services\Obj;

class Resource implements StubProvider
{
    private string $root;
    private string $namespace;
    private string $resource;
    private Obj $package;

    public function __construct(Choices $choices, $resource)
    {
        $this->package = $choices->get('package');
        $this->root = $choices->params()->get('root');
        $this->namespace = $choices->params()->get('namespace');
        $this->resource = $resource;
    }

    public function prepare(): void
    {
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        return $this->path($this->resource);
    }

    public function fromTo(): array
    {
        $segments = (new Collection(explode('\\', $this->namespace)))->slice(0, 2);

        return [
            '${year}' => Carbon::now()->format('Y'),
            '${vendor}' => $this->package->get('vendor'),
            '${package}' => $this->package->get('name'),
            '${namespace}' => $segments->implode('\\'),
            '${Vendor}' => $segments->first(),
            '${Package}' => $segments->last(),
        ];
    }

    public function stub(): string
    {
        return Stub::get($this->resource);
    }

    private function path(?string $filename = null): string
    {
        return (new Collection([$this->root, $filename]))
            ->filter()->implode(DIRECTORY_SEPARATOR);
    }
}
