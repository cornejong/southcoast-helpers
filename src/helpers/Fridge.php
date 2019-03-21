<?php

namespace SouthCoast\Helpers;

class Fridge
{

    private static $fridge;

    public static function get(string $query, bool $subtract_query = true, bool $do_rebuild = true)
    {
        return ArrayHelper::get($query, self::$fridge, $subtract_query, $do_rebuild);
    }

    public static function set(string $query, $value)
    {
        self::$fridge = ArrayHelper::add($query, $value, self::$fridge);
    }

    public static function has(string $query)
    {
        return ArrayHelper::get($query, self::$fridge) !== null ? true : false;
    }

    public static function clear()
    {
        self::$fridge = [];
    }

    public static function export()
    {
        return ArrayHelper::sanitize(self::$fridge);
    }

}
