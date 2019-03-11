<?php

namespace SouthCoast\Helpers;

class Xml
{
    public static function isValid(string $xml): bool
    {
        $reader = new XMLReader();
        $reader->XML($xml);

        $valid = $reader->isValid();

        $reader->close();
        unset($reader);

        return $valid; 
    }

    public static function stringify($data): string
    {
        # code...
    }

    public static function encode($data): string
    {
        return self::stringify($data);
    }

    public static function parse(string $string, $array = true)
    {
        return ($array) ? self::parseToArray($string) : self::parseToObject($string);
    }

    public static function parseToArray(string $data)
    {
        if (!self::isValid($data)) {
            return false;
        }

        $array = simplexml_load_string($data);

        return ArrayHelper::sanitize($array);
    }

    public static function parseToObject(string $data)
    {
        if (!self::isValid($data)) {
            return false;
        }

        $array = simplexml_load_string($data);
        $array = ArrayHelper::sanitize($array);

        return ArrayHelper::objectify($array);
    }
}