<?php

namespace LaravelEnso\Cli\Services;

class Symbol
{
    private const Check = '&#x2713;';
    private const Cross = '&#x2717;';
    private const Exclamation = '&#x00021;';

    public static function bool(bool $status)
    {
        return $status ? self::check() : self::cross();
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
        $decodedSymbol = html_entity_decode($symbol, ENT_NOQUOTES, 'UTF-8');

        return "<fg={$color}>{$decodedSymbol}</>";
    }
}
