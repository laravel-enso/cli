<?php

namespace LaravelEnso\Cli\Services\Writers\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelEnso\Helpers\Classes\Obj;

class Segments
{
    private static Collection $segments;
    private static bool $ucfirst;

    public static function get(bool $full = true)
    {
        $segments = $full ? self::$segments : self::$segments->slice(0, -1);

        return $segments->when(
            self::$ucfirst,
            fn ($segments) => $segments->map(fn ($segment) => Str::ucfirst($segment))
        );
    }

    public static function count()
    {
        return self::$segments->count();
    }

    public static function set(?Obj $group)
    {
        if ($group) {
            self::$segments = new Collection(
                explode('.', $group->get('name'))
            );
        }
    }

    public static function ucfirst(bool $ucfirst = true)
    {
        self::$ucfirst = $ucfirst;
    }
}
