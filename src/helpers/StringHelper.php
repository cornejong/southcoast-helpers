<?php

namespace SouthCoast\Helpers;

use \Error;

class StringHelper
{
    /**
     * @param string $needle
     * @param string $string
     */
    public static function contains(string $needle, string $string)
    {
        return strpos($string, $needle) !== false ? true : false;
    }

    /**
     * @param string $needle
     * @param string $string
     * @return mixed
     */
    public static function startsWith(string $needle, string $string)
    {
        return $needle === substr($string, 0, strlen($needle)) ? true : false;
    }

    /**
     * @param string $needle
     * @param string $string
     * @return mixed
     */
    public static function endsWith(string $needle, string $string)
    {
        return $needle === substr($string, -strlen($needle), strlen($needle)) ? true : false;
    }

    /**
     * @param $data
     */
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

    /**
     * @param string $string
     * @return mixed
     */
    public static function explodeCamelCase(string $string): array
    {
        preg_match_all('/((?:^|[A-Z])[a-z]+)/', $string, $matches);
        return $matches[0];
    }

    /**
     * removes all special characters from a given string
     *
     * @param string $text
     * @return string
     */
    public static function clean(string $text): string
    {
        $utf8 = [
            '/[áàâãªä]/u' => 'a',
            '/[ÁÀÂÃÄ]/u' => 'A',
            '/[ÍÌÎÏ]/u' => 'I',
            '/[íìîï]/u' => 'i',
            '/[éèêë]/u' => 'e',
            '/[ÉÈÊË]/u' => 'E',
            '/[óòôõºö]/u' => 'o',
            '/[ÓÒÔÕÖ]/u' => 'O',
            '/[úùûü]/u' => 'u',
            '/[ÚÙÛÜ]/u' => 'U',
            '/ç/' => 'c',
            '/Ç/' => 'C',
            '/ñ/' => 'n',
            '/Ñ/' => 'N',
            '/–/' => '-', // UTF-8 hyphen to "normal" hyphen
            '/[’‘‹›‚]/u' => ' ', // Literally a single quote
            '/[“”«»„]/u' => ' ', // Double quote
            '/ /' => ' ', // nonbreaking space (equiv. to 0x160)
        ];

        $result = preg_replace(array_keys($utf8), array_values($utf8), $text);

        return $result ?? $text;
    }

    /**
     * Converts a stringified value into its correct type
     *
     * @param string $string
     */
    public static function getRealType(string $string)
    {
        if (Number::isFloat($string)) {
            return (float) Number::convert2Float($string);
        }

        if (Number::isInteger($string)) {
            return (int) Number::convert2Integer($string);
        }

        if (strtolower($string) === 'true') {
            return true;
        }

        if (strtolower($string) === 'false') {
            return false;
        }

        if (strtolower($string) === 'null') {
            return null;
        }

        return $string;
    }
}
