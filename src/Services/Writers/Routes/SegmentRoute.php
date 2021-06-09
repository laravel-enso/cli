<?php

namespace LaravelEnso\Cli\Services\Writers\Routes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\Contracts\StubProvider;
use LaravelEnso\Cli\Services\Choices;
use LaravelEnso\Cli\Services\Writers\Helpers\Directory;
use LaravelEnso\Cli\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\Services\Writers\Helpers\Stub;

class SegmentRoute implements StubProvider
{
    private string $group;
    private Collection $segments;

    public function __construct(Choices $choices, Collection $segments)
    {
        $this->group = $choices->get('permissionGroup')->get('name');
        $this->segments = $segments;
    }

    public function prepare(): void
    {
        Path::segments(false);
        Directory::prepare($this->path());
    }

    public function filePath(): string
    {
        return $this->path("{$this->segments->last()}.js");
    }

    public function fromTo(): array
    {
        $segment = $this->segments->last();
        $depth = $this->segments->count();

        return [
            '${segment}' => $segment,
            '${breadcrumb}' => Collection::wrap(explode('_', Str::snake($segment)))->implode(' '),
            '${permissionGroup}' => $this->group,
            '${relativePath}' => $depth === 1 ? DIRECTORY_SEPARATOR.$segment : $segment,
        ];
    }

    public function stub(): string
    {
        $stub = $this->segments->count() === Segments::count()
            ? 'parentSegment'
            : 'segment';

        return Stub::get($stub);
    }

    private function path(?string $filename = null): string
    {
        return Path::get([
            'client', 'src', 'js', 'routes', ...$this->segments->slice(0, -1),
        ], $filename, true);
    }
}
