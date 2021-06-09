<?php

namespace LaravelEnso\Cli\Services\Writers\Helpers;

use Illuminate\Support\Collection;

class Namespacer
{
    private static string $prefix;

    public static function get(array $segments, bool $full = false)
    {
        return Collection::wrap([
            self::$prefix, ...$segments, ...Segments::get($full),
        ])->implode('\\');
    }

    public static function prefix(string $prefix)
    {
        self::$prefix = $prefix;
    }
}
