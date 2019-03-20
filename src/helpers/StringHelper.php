<?php

namespace SouthCoast\Helpers;

use \Error;

class StringHelper
{

    public static function contains(string $needle, string $string)
    {
        return strpos($string, $needle) !== false ? true : false;
    }

    public static function startsWith(string $needle, string $string)
    {
        return $needle === substr($string, 0, strlen($needle)) ? true : false;
    }

    public static function endsWith(string $needle, string $string)
    {
        return $needle === substr($string, -strlen($needle), strlen($needle)) ? true : false;
    }

    public function stringify($data)
    {
        switch (gettype($data)) {
            case 'string':
                return (string) $data;
                break;

            case 'integer':
            case 'double':
                return '' . $data . '';
                break;

            case 'boolean':
                return (string) ($data) ? 'true' : false;
                break;

            case 'NULL':
                return 'NULL';
                break;

            case 'array':
            case 'object':
                return strval($data);
                break;

            default:
                throw new Error('Unsupported Type for to string conversion! Provided Type: ' . gettype($data), 1);
                break;
        }
    }

    public static function explodeCamelCase(string $string): array
    {
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $string, $matches);
        return $matches[0];
    }
}
