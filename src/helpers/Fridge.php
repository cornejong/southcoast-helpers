<?php

namespace SouthCoast\Helpers;

class Fridge
{
    /**
     * @var mixed
     */
    private static $fridge;

    /**
     * @param string $query
     * @param bool $subtract_query
     * @param bool $do_rebuild
     */
    public static function get(string $query, bool $subtract_query = true, bool $do_rebuild = true)
    {
        return ArrayHelper::get($query, self::$fridge, $subtract_query, $do_rebuild);
    }

    /**
     * @param string $query
     * @param mixed $value
     */
    public static function set(string $query, $value)
    {
        self::$fridge = ArrayHelper::add($query, $value, self::$fridge);
    }

    /**
     * @param string $query
     */
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
        return self::$fridge;
    }

}
