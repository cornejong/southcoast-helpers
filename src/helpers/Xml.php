<?php

namespace SouthCoast\Helpers;

use SouthCoast\Helpers\Objects\XmlObject;

use XMLReader;

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

    public static function stringify(string $openingTag, array $data, $version = '1.0'): string
    {
        return (new XmlObject($openingTag, $version))->loadArray($data)->getXml();
    }

    public static function encode(string $openingTag, array $data, $version = '1.0'): string
    {
        return self::stringify($openingTag, $data, $version);
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