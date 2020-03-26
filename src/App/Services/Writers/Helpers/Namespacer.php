<?php

namespace LaravelEnso\Cli\App\Services\Writers\Helpers;

use Illuminate\Support\Collection;

class Namespacer
{
    private static string $prefix;

    public static function get(array $segments, bool $full = false)
    {
        return (new Collection([
            self::$prefix, ...$segments, ...Segments::get($full),
        ]))->implode('\\');
    }

    public static function prefix(string $prefix)
    {
        self::$prefix = $prefix;
    }
}
