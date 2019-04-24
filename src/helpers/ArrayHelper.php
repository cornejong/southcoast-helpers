<?php

/*
 * Created on Wed Okt 12 2018
 *
 * Copyright (c) 2019 SouthCoast
 */

namespace SouthCoast\Helpers;

/**
 * A tool set for working with arrays in php
 * @author SouthCoast <hello@southcoast.tech>
 * @category Helper
 * @version 1.0.0
 * @package SouthCoast\Helpers
 */
class ArrayHelper
{
    const FLAT_QUERY_EXPRESSION_OPENER = '/^';
    const FLAT_QUERY_EXPRESSION_CLOSER = '.*$/';

    const FLAT_QUERY_EXPRESSION_WILDCARD = '(.*)';

    const FLAT_QUERY_EXPRESSION_STRING_OPEN = '(';
    const FLAT_QUERY_EXPRESSION_STRING_CLOSE = ')';

    const FLAT_QUERY_EXPRESSION_NUMERIC_OPEN = '(\[';
    const FLAT_QUERY_EXPRESSION_NUMERIC_CLOSE = '\])';

    const FLAT_QUERY_EXPRESSION_SEPARATOR = '\.';

    /**
     * Sanitizes the provided data, mainly designed for cleaning up objects or classes
     *
     * @param mixed $data           The to be sanitized resource
     * @param boolean $array        Should it return an array? (if false, will return a StdClass object)
     * @return array|object         Returns an Array by default, returns an array when $array = false
     */
    public static function sanitize($data, bool $array = true)
    {
        return json_decode(json_encode($data), $array);
    }

    /**
     * Reference alias of SouthCoast\Helpers\ArrayHelper::sanitize()
     *
     * @param mixed $data           The to be sanitized resource
     * @param boolean $array        Should it return an array? (if false, will return a StdClass object)
     * @return void                 Returns nothing, data is passed by reference
     */
    public static function sanitizer(&$data, bool $array = true): void
    {
        $data = self::sanitize($data, $array);
    }

    /**
     * Objectifies an Array
     *
     * @param array $array          The to be objectified Array
     * @return StdClass|object      The objectified resource
     */
    public static function objectify(array $array)
    {
        return self::sanitize($array, false);
    }

    /**
     * Checks if the provided keys are set in the array
     * fills the $missing array with the missing keys
     *
     * Checks only for missing keys, not for empty values
     *
     * @param array $keys       The keys that should be set
     * @param array $array      The resource where the keys should be in
     * @param array $missing    The array of missing elements
     * @param boolean $strict   Enter Strict mode (default off)
     * @return boolean          Returns true if all keys where pressent, false if not
     */
    public static function keysAreSet(array $keys, array $array, &$missing, bool $strict = false): bool
    {
        $missing = [];

        if (is_object($array)) {
            self::sanitizer($array);
        }

        foreach ($keys as $key) {
            if (!in_array($key, $array, $strict)) {
                $missing[] = $key;
            }
        }

        return (empty($missing)) ? true : false;
    }

    /**
     * Search an array for a specified value
     *
     * @param string|int|bool $value
     * @param array $array
     * @param boolean $strict
     * @return void
     */
    public static function search($value, array $array, bool $strict = false)
    {
        $flat_array = self::flatten($array);
        $found = array_search($value, $flat_array, $strict);

        if (!$found) {
            return false;
        }

        return self::cleanUpFlatQueryString($found);

        // $dove = self::arrayDive(explode('.', $found), $array);
        // return self::cleanupRebuild($dove);
    }

    /**
     * @param string $query
     * @param array $array
     * @param $found
     * @param nullbool $strict
     */
    public static function searchByQuery(string $query, array $array, &$found = null, bool $strict = false): bool
    {
        $flat_array = self::flatten($array);
        $query = self::renderSearchQuery($query);

        $found = [];

        foreach (self::get($query['string'], $array, true, false) as $key => $value) {
            if (preg_match($query['pattern'], $key, $matches)) {
                $strict_statement = ($value === $query['requested_value']);
                $non_strict_statenent = ($value == $query['requested_value']);

                if (($strict && $strict_statement) || (!$strict && $non_strict_statenent)) {
                    $found[] = self::cleanUpFlatQueryString($key);
                }
            }
        }

        if (count($found) == 1) {
            $found = $found[0];
        }

        return (empty($found)) ? false : true;
    }

    /**
     * @param string $query
     */
    public static function renderSearchQuery(string $query): array
    {
        $query_array = explode(' ', $query);

        $string = array_shift($query_array);
        $operator = array_shift($query_array);
        $value = implode(' ', $query_array);

        return [
            'pattern' => self::buildFlatQueryExpression($string),
            'string' => $string,
            'requested_value' => $value,
            'comparison_operator' => $operator,
        ];
    }

    /**
     * @param string $query
     */
    public static function cleanUpFlatQueryString(string $query)
    {
        return preg_replace('/\[(\d*)\]/', '$1', $query);
    }

    /**
     * @param array $accepted
     * @param array $provided
     * @param $unaccepted
     */
    public static function containsUnAcceptedElements(array $accepted, array $provided, &$unaccepted): bool
    {
        $unaccepted = [];

        foreach ($provided as $item) {
            if (!in_array($item, $accepted)) {
                $unaccepted[] = $item;
            }
        }

        return empty($unaccepted) ? false : true;
    }

    /**
     * @param array $parameters
     * @param array $data
     * @param $missing
     * @param bool $strict
     */
    public static function requiredPramatersAreSet(array $parameters, array $data, &$missing, bool $strict = false): bool
    {
        $missing = [];
        $isAssoc = false;

        /* If the array is associative */
        if (self::isAssoc($data)) {
            $isAssoc = true;
            $data = array_keys($data);
        }

        foreach ($parameters as $param) {
            if (!in_array($param, $data, $strict)) {
                $missing[] = $param;
            }

            if ($isAssoc && !in_array($param, $missing) && empty($data[$param])) {
                $missing[] = $param;
            }
        }

        return (empty($missing)) ? true : false;
    }

    /**
     * @param $input
     * @param $parent
     * @return mixed
     */
    public static function flatten($input, $parent = null): array
    {
        $array = [];

        foreach ($input as $key => $value) {
            if (is_numeric($key)) {
                $key = '[' . $key . ']';
            }

            if (is_array($value)) {
                $array = array_merge_recursive($array, self::flatten($value, (isset($parent) ? $parent . '.' : '') . $key));
            } else {
                $array[(isset($parent) ? $parent . '.' : '') . $key] = $value;
            }
        }

        return $array;
    }

    /**
     * @param array $keys
     * @param $array
     * @return mixed
     */
    public static function arrayDive(array $keys, $array)
    {
        $return = [];
        $index = array_shift($keys);

        if (!isset($keys[0])) {
            return $array;
        } else {
            $return = self::makeArray($keys, $array[$index]);
        }

        return $array;
    }

    /**
     * @param array $keys
     * @param $value
     * @return mixed
     */
    public static function makeArray(array $keys, $value)
    {
        $array = [];
        $index = (string) array_shift($keys);

        if (count($keys) == 0) {
            $array[$index] = $value;
        } else {
            $array[$index] = self::makeArray($keys, $value);
        }

        return $array;
    }

    /**
     * @param array $map
     */
    public static function rebuildArray(array $map): array
    {
        $array = [];

        foreach ($map as $key => $value) {
            $key_array = explode('.', $key);
            if (count($key_array) == 1) {
                $array["{$key}"] = $value;
            } else {
                $array = array_merge_recursive($array, self::makeArray($key_array, $value));
            }
        }

        return self::cleanupRebuild($array);
    }

    /**
     * @param array $array
     */
    public static function cleanupRebuild(array $array): array
    {
        return json_decode(preg_replace('/\[(\d*)\]/', '$1', json_encode($array)), true);
    }

    /**
     * @param string $query
     * @param array $array
     * @param bool $subtractQuery
     * @param bool $doRebuild
     * @return string|array
     */
    public static function get(string $query, array $array, bool $subtractQuery = true, bool $doRebuild = true)
    {
        $flat = self::flatten($array);

        $pattern = self::buildFlatQueryExpression($query);
        $tmp = [];

        foreach ($flat as $key => $value) {
            if (preg_match($pattern, $key)) {
                if ($subtractQuery) {
                    $newKey = str_replace(self::buildFlatQueryString($query), '', $key);

                    if ($newKey != '' && $newKey[0] == '.') {
                        $newKey = ltrim($newKey, '.');
                    }

                    $tmp[(empty($newKey)) ?: $newKey] = $value;
                } else {
                    $tmp[$key] = $value;
                }
            }
        }

        if (empty($tmp)) {
            return null;
        }

        if (count($tmp) === 1) {
            return array_shift($tmp);
        }

        return $doRebuild ? self::rebuildArray($tmp) : $tmp;
    }

    /**
     * @param string $index
     * @param $value
     * @param array $data
     */
    public static function add(string $index, $value, array &$data)
    {
        $data = self::flatten($data);

        if (is_array($value) || is_object($value)) {
            $tmp = self::flatten($value, $index);
        } else {
            $tmp = [$index => $value];
        }

        $data = self::rebuildArray(array_merge($data, $tmp));

        return $data;
    }

    /**
     * @param string $query
     * @param array $array
     * @param bool $subtractQuery
     * @param bool $doRebuild
     */
    public static function getParent(string $query, array $array, bool $subtractQuery = true, bool $doRebuild = true)
    {
        $query_array = explode('.', $query);
        $child = array_pop($query_array);
        return self::get(implode('.', $query_array), $array, $subtractQuery, $doRebuild);
    }

    /**
     * @param array $array
     * @param $queries
     */
    public static function getMultiple(array $array, ...$queries)
    {
        $tmp = [];

        foreach ($queries as $query) {
            $tmp = array_merge_recursive($tmp, self::get($query, $array, true, false));
        }

        return self::rebuildArray($tmp);
    }

    /**
     * @param string $query
     * @return mixed
     */
    public static function buildFlatQueryString(string $query): string
    {
        $string = '';

        foreach (explode('.', $query) as $index => $i) {
            if (is_numeric($i)) {
                $i = '[' . $i . ']';
            }

            $string .= ($index == 0) ? $i : '.' . $i;
        }

        return $string;
    }

    /**
     * @param string $query
     * @return mixed
     */
    public static function buildFlatQueryExpression(string $query): string
    {
        $expression = self::FLAT_QUERY_EXPRESSION_OPENER;

        foreach (explode('.', $query) as $index => $i) {
            if (is_numeric($i)) {
                $i = self::FLAT_QUERY_EXPRESSION_NUMERIC_OPEN . $i . self::FLAT_QUERY_EXPRESSION_NUMERIC_CLOSE;
            } elseif ($i === '?' || $i === '*') {
                $i = self::FLAT_QUERY_EXPRESSION_WILDCARD;
            } else {
                $i = self::FLAT_QUERY_EXPRESSION_STRING_OPEN . $i . self::FLAT_QUERY_EXPRESSION_STRING_CLOSE;
            }

            $expression .= ($index == 0) ? $i : self::FLAT_QUERY_EXPRESSION_SEPARATOR . $i;
        }

        $expression .= self::FLAT_QUERY_EXPRESSION_CLOSER;

        return $expression;
    }

    /**
     * @param string $query
     * @return string
     */
    public static function prepareQuery(string $query): string
    {
        $query_array = explode('.', $query);
        $tmp = '';
        foreach ($query_array as $index => $value) {
            if (is_numeric($value)) {
                $tmp .= '[' . $value . ']';
            } else {
                $tmp .= $value;
            }

            if ($index !== self::lastKey($query_array)) {
                $tmp .= '.';
            }
        }

        return $tmp;
    }

    /**
     * @param array $array
     */
    public static function isAssoc(array $array)
    {
        /* Check if its empty */
        if ([] === $array) {
            /* Than it can in no way be an associative array */
            return false;
        }

        /* Check if the array keys are basically a number range */
        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Counts all elements in an array recursivly
     * Not only the top level elements in a multidimentional array
     *
     * @param mixed $array
     * @return integer
     */
    public function recursiveCount($array): int
    {
        return (int) count($array, COUNT_RECURSIVE);
    }

    /**
     * indexes the provided Array by the index
     * Additionally you van provide a callable function
     *
     * @param array $array
     * @param string $index
     * @param callable $callback
     * @param bool $returnObject = false
     * @param bool $skipIfMissing = false
     * @return array
     */
    public static function index(array $array, string $index, callable $callback = null, bool $skipIfMissing = true)
    {
        $tmp = [];

        foreach ($array as $key => &$value) {
            if (!is_null($callback) && is_callable($callback)) {
                $callback($key, $value, $array);
            }
        }

        foreach ($array as $key => $value) {
            if (!isset($value[$index])) {
                if ($skipIfMissing) {
                    continue;
                } else {
                    throw new \Exception('Specified Index not present in array element with key: ' . $index, 1);
                }
            }

            if (isset($tmp[$value[$index]])) {
                $tmp[$value[$index]][] = $value;
            } else {
                $tmp[$value[$index]] = $value;
            }
        }

        return self::sanitize($tmp);
    }

    /**
     * Maps the values of the $array to new keys
     * Adds support for multidimensional arrays via dot notation
     *
     * $map = [
     *      The KEY is the to be used key for the Array
     *      'field' is the value origin
     *      'New_Name' => ['field' => 'old_name'],
     *
     *      Add 'value' to add custom value or value mutation
     *      'Email' => ['value' => 'Some Other Value'],
     *      Or just add the value
     *      'Email' => 'Some Other Value
     *
     *      Add '.' separators for sub objects
     *      'Email.primary' => ['field' => 'email'],
     *
     *      Use '0' for arrays
     *      'Addresses.0.street' => ['field => 'address_1_line_1'],
     *
     *      Get a value from a multidimensional source
     *      'isDefault' => ['field' => 'meta.system.default'],
     *
     *      Add the 'or' field to supply a value that will used if the value from the original array is not found or null
     *      'automated' => ['field' => 'system.automated', 'or' => 'nope, not automated']
     *
     *      Use the 'add' field to specify if this field should be added
     *      'someAwesomeField' => ['field' => 'getMyValue', 'add' => false] // Wont be added
     *      'someAwesomeField' => ['field' => 'getMyValue', 'add' => true] // Will be added
     *
     *      Add an alternative field to the mapping if the original field is missing or returned null
     *      'arbitraryKey' => ['field => 'getItFromHere', 'alt_field' => 'or_from_here', 'or' => 'a default value']
     *
     * @param array $map
     * @param array $array
     * @return array
     */
    public static function map(array $map, array $array)
    {
        /* First, flatten the array */
        $array = self::flatten($array);

        /* Create the temp array */
        $tmp = [];

        /* Loop over all the values in the mapping */
        foreach ($map as $key => $item) {
            if (!is_array($item)) {
                $tmp[$key] = $item;
            }

            /* Check if there was an 'add' value provided in the mapping */
            if (array_key_exists('add', $item)) {
                /* If its anything but true */
                if ($item['add'] !== true) {
                    /* Continue onto the next one */
                    continue;
                }

                /* else just add the field */
            }

            /* Check if the field value is a string */
            if (!is_string($item['field'])) {
                /* if not, throw an Exception */
                throw new \Exception('The field value should be a string! Provided key: \'' . $key . '\'', 1);
            }

            /* Check if there was a value provided in the mapping */
            if (array_key_exists('value', $item)) {
                /* Add that value to the array */
                $tmp[$key] = $item['value'];
                /* And continue */
                continue;
            }

            /* Get the value from the array */
            $value = self::get($item['field'], $array);

            /* Check if we could find the field and its value, Check if there was an 'alt_field' value provided in the mapping */
            if ($value === null && array_key_exists('alt_field', $item) && !empty($item['alt_field'])) {
                /* Get the value from that field instead */
                $value = self::get($item['alt_field'], $array);
            }

            /* Check if there was an or field specified and the current value is null */
            if ($value === null && array_key_exists('or', $item)) {
                /* Use the value of 'or' as the new value */
                $value = $item['or'];
            }

            /* Finally, add the value to the tmp array */
            $tmp[$key] = $value;
        }

        /* Return the rebuild array */
        return self::rebuildArray($tmp);
    }

    public static function manage()
    {
        # code...
    }

    /**
     * Compiles multiple arrays into one based on the provided mapping
     *
     * @param array $map
     * @param callable $callback
     * @param array ...$arrays
     * @return array
     */
    public static function compile(array $map, callable $callback, ...$arrays)
    {
        # code...
    }

    /**
     * @param $array
     * @param $callback
     * @param $parameters
     * @return void
     */
    public static function walk(array &$array, $callback, ...$parameters)
    {
        foreach ($array as &$element) {
            $callback($element, ...$parameters);
        }
    }

    /**
     * Cleans all the values in the provided array
     *
     * @param $array        The to-be cleaned array
     * @return mixed        The cleaned array
     */
    public static function clean(array $array): array
    {
        /* loop overt all the values in the array */
        array_walk_recursive($array, function (&$item) {
            /* Check if the value is a string */
            if (is_string($item)) {
                /* Clean the string */
                $item = StringHelper::clean($item);
            }
        });
        /* Return the cleaned array */
        return $array;
    }

    /**
     * Splits the array keys and values into separate arrays
     *
     * @param array $array
     * @return array
     */
    public static function split(array $array): array
    {
        return [
            array_keys($array),
            array_values($array),
        ];
    }

    /**
     * @param array $array
     */
    public static function lastKey(array $array)
    {
        return array_pop(array_keys($array)) ?? null;
    }

    /**
     * @param array $array
     */
    public static function lastValue(array $array)
    {
        return array_pop($array);
    }

}
