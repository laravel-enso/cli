<?php

namespace LaravelEnso\Cli\App\Services\Writers\Helpers;

use Illuminate\Support\Collection;

class Path
{
    private static ?string $root = null;
    private static bool $segments;

    public static function get(array $segments, ?string $filename = null, bool $full = false)
    {
        $collection = (new Collection([self::$root, ...$segments]));

        if (self::$segments) {
            $collection = $collection->concat(Segments::get($full));
        }

        return $collection->push($filename)
            ->filter()->implode(DIRECTORY_SEPARATOR);
    }

    public static function root(string $root)
    {
        self::$root = $root;
    }

    public static function segments(bool $segments = true)
    {
        self::$segments = $segments;
    }
}
