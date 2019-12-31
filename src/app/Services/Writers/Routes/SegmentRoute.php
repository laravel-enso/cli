<?php

namespace LaravelEnso\Cli\App\Services\Writers\Routes;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Cli\App\Contracts\StubProvider;
use LaravelEnso\Cli\App\Services\Choices;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Path;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Segments;
use LaravelEnso\Cli\App\Services\Writers\Helpers\Stub;
use LaravelEnso\Helpers\App\Classes\Obj;

class SegmentRoute implements StubProvider
{
    private string $group;
    private Collection $segments;

    public function __construct(Choices $choices, Collection $segments)
    {
        $this->group = $choices->get('permissionGroup')->get('name');
        $this->segments = $segments;

        Path::segments(false);
    }

    public function path(?string $filename = null): string
    {
        return Path::get([
            'client', 'src', 'js', 'routes', ...$this->segments->slice(0, -1),
        ], $filename, true);
    }

    public function filename(): string
    {
        return "{$this->segments->last()}.js";
    }

    public function fromTo(): array
    {
        $segment = $this->segments->last();
        $depth = $this->segments->count();

        return [
            '${segment}' => $segment,
            '${breadcrumb}' => (new Collection(explode('_', Str::snake($segment))))->implode(' '),
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
}
