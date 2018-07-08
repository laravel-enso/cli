<?php

namespace LaravelEnso\StructureManager\app\Writers\Helpers;

class Symbol
{
    const Check = '&#x2713;';
    const Cross = '&#x2717;';
    const Exclamation = '&#x00021;';

    public static function bool($bool)
    {
        return $bool
            ? self::check()
            : self::cross();
    }

    public static function check()
    {
        return self::output(self::Check, 'green');
    }

    public static function cross()
    {
        return self::output(self::Cross, 'red');
    }

    public static function exclamation()
    {
        return self::output(self::Exclamation, 'yellow');
    }

    private static function output($symbol, $color)
    {
        return '<fg='.$color.'>'.html_entity_decode($symbol, ENT_NOQUOTES, 'UTF-8').'</>';
    }
}
