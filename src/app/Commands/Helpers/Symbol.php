<?php

namespace LaravelEnso\StructureManager\app\Classes\Helpers;

class Symbol
{
    public static function bool($bool)
    {
        return $bool
            ? self::check()
            : self::cross();
    }

    public static function check()
    {
        return '<fg=green>'.html_entity_decode('&#x2713;', ENT_NOQUOTES, 'UTF-8').'</>';
    }

    public static function cross()
    {
        return '<fg=red>'.html_entity_decode('&#x2717;', ENT_NOQUOTES, 'UTF-8').'</>';
    }
}
