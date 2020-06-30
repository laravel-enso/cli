<?php

namespace LaravelEnso\Cli\Services\Writers\Helpers;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;

class Stub
{
    private static string $folder;

    public static function get(string $file)
    {
        [$name] = explode('.', $file);

        $filePath = (new Collection([
            __DIR__, '..', 'stubs', self::$folder, "{$name}.stub",
        ]))->implode(DIRECTORY_SEPARATOR);

        return File::get($filePath);
    }

    public static function folder(string $folder)
    {
        self::$folder = $folder;
    }
}
