<?php

namespace LaravelEnso\Cli\Services\Writers\Helpers;

use Illuminate\Support\Facades\File;

class Directory
{
    public static function prepare(string $directory)
    {
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }
}
