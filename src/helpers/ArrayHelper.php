<?php

/*
 * Created on Wed Okt 12 2018
 *
 * Copyright (c) 2019 SouthCoast
 */


namespace SouthCoast\Helpers;

/**
 * A toolset for working with arrays in php
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

    const FLAT_QUERY_EXPRESSION_SEPERATOR = '\.';



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
    public static function sanitizer(&$data, bool $array = true) : void
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
    public static function keysAreSet(array $keys, array $array, &$missing, bool $strict = false) : bool
    {
        $missing = [];

        if(is_object($array)) {
            self::sanitizer($array);
        }

        foreach($keys as $key) {
            if(!in_array($key, $array, $strict)) {
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

        if(!$found) {
            return false;
        }

        return self::cleanUpFlatQueryString($found);

        // $dove = self::arrayDive(explode('.', $found), $array);
        // return self::cleanupRebuild($dove);
    }

    public static function searchByQuery(string $query, array $array, &$found = null, bool $strict = false) : bool
    {
        $flat_array = self::flatten($array);
        $query = self::renderSearchQuery($query);

        $found = [];

        foreach (self::get($query['string'], $array, true, false) as $key => $value) {

            if(preg_match($query['pattern'], $key, $matches)) {
                $strict_statement = ($value === $query['requested_value']);
                $non_strict_statenent = ($value == $query['requested_value']);

                if(($strict && $strict_statement) || (!$strict && $non_strict_statenent)) {
                    $found[] = self::cleanUpFlatQueryString($key);
                }
            } 
        }

        if(count($found) == 1) {
            $found = $found[0];
        }

        return (empty($found)) ? false : true;
    }

    public static function renderSearchQuery(string $query) : array
    {
        $query_array = explode(' ', $query);

        return [
            'pattern' => self::buildFlatQueryExpression($query_array[0]),
            'string' => $query_array[0],
            'requested_value' => $query_array[2],
            'comparison_operator' => $query_array[1],
        ];
    }

    public static function cleanUpFlatQueryString(string $query)
    {
        return preg_replace('/\[(\d*)\]/', '$1', $query);
    }


    public static function requiredPramatersAreSet(array $parameters, array $data, &$missing, bool $strict = false) : bool
    {
        $missing = [];
        $isAssoc = false;

        /* If the array is associative */
        if(self::isAssoc($data)) {
            $isAssoc = true;
            $data = array_keys($data);   
        } 

        foreach($parameters as $param) {
            if(!in_array($param, $data, $strict)) {
                $missing[] = $param;
            }

            if($isAssoc && !in_array($param, $missing) && empty($data[$param])) {
                $missing[] = $param;
            }
        }

        return (empty($missing)) ? true : false;
    }
    
    public static function flatten($input, $parent = null) : array
    {
        $array = [];
        
        foreach($input as $key => $value) {
            if(is_numeric($key)) {
                $key = '[' . $key . ']';
            }
            
            if(is_array($value)) {
                $array = array_merge_recursive($array, self::flatten($value, (isset($parent) ? $parent . '.' : '') . $key));
            } else {
                $array[(isset($parent) ? $parent . '.' : '') . $key] = $value;
            }
        }
        
        return $array;
    }
    
    public static function arrayDive(array $keys, $array)
    {
        $return = [];   
        $index = array_shift($keys);
        
        if(!isset($keys[0])) {
            return $array;
        } else {   
            $return = self::makeArray($keys,$array[$index]);    
        }
        
        return $array;
    }

    public static function makeArray(array $keys, $value)
    {
        $array = [];   
        $index = (string) array_shift($keys);
        
        if(count($keys) == 0){
            $array[$index] = $value;
        } else {   
            $array[$index] = self::makeArray($keys,$value);    
        }
    
        return $array;
    }
    
    
    public static function rebuildArray(array $map) : array
    {
        $array = [];
    
        foreach($map as $key => $value) {
            $key_array = explode('.', $key);
            if(count($key_array) == 1) {
                $array["{$key}"] = $value;
            } else {
                $array = array_merge_recursive($array, self::makeArray($key_array, $value));
            }
        }
        
        return self::cleanupRebuild($array);
    }

    public static function cleanupRebuild(array $array) : array
    {
        return json_decode(preg_replace('/\[(\d*)\]/', '$1', json_encode($array)), true);
    }

    public static function get(string $query, array $array, bool $subtractQuery = true, bool $doRebuild = true)
    {
        $flat = self::flatten($array);

        $pattern = self::buildFlatQueryExpression($query);
        $tmp = [];

        foreach ($flat as $key => $value) {
            if(preg_match($pattern, $key, $matches)) {
                if($subtractQuery) {
                    $newKey = str_replace(self::buildFlatQueryString($query), '', $key);

                    if($newKey != '' && $newKey[0] == '.') {
                        $newKey = ltrim($newKey, '.');
                    }

                    $tmp[(empty($newKey)) ? : $newKey] = $value;

                } else {
                    $tmp[$key] = $value;
                }  
            }
        }

        if(empty($tmp)) {
            return null;
        }
            
        if(count($tmp) == 1) {
            $key = array_keys($tmp)[0];
            $rebuild = $tmp[$key];
        } else {
            $rebuild = self::rebuildArray($tmp);
        }

        return ($doRebuild) ? $rebuild : $tmp;
    }

    public static function getParent(string $query, array $array, bool $subtractQuery = true, bool $doRebuild = true)
    {
        $query_array = explode('.', $query);
        $child = array_pop($query_array);
        return self::get(implode('.', $query_array), $array, $subtractQuery, $doRebuild);
    }

    public static function getMultiple(array $array, ...$queries) {
        $tmp = [];

        foreach($queries as $query) {
            $tmp = array_merge_recursive($tmp, self::get($query, $array, true, false));
        }

        return self::rebuildArray($tmp);
    }

    public static function buildFlatQueryString(string $query) : string
    {
        $string = '';

        foreach(explode('.', $query) as $index => $i) {

            if(is_numeric($i)) {
                $i = '[' . $i . ']';
            }

            $string .= ($index == 0) ? $i : '.' . $i;
        }

        return $string;
    }

    public static function buildFlatQueryExpression(string $query) : string
    {
        $expression = self::FLAT_QUERY_EXPRESSION_OPENER;

        foreach(explode('.', $query) as $index => $i) {

            if(is_numeric($i)) {
                $i = self::FLAT_QUERY_EXPRESSION_NUMERIC_OPEN . $i . self::FLAT_QUERY_EXPRESSION_NUMERIC_CLOSE;
            } elseif($i == '?') {
                $i = self::FLAT_QUERY_EXPRESSION_WILDCARD;
            } else {
                $i = self::FLAT_QUERY_EXPRESSION_STRING_OPEN . $i . self::FLAT_QUERY_EXPRESSION_STRING_CLOSE;
            }

            $expression .= ($index == 0) ? $i : self::FLAT_QUERY_EXPRESSION_SEPERATOR . $i;
        }

        $expression .= self::FLAT_QUERY_EXPRESSION_CLOSER;

        return $expression;
    }

    public static function isAssoc(array $array)
    {
        if ([] === $array) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    /**
     * Counts all elements in an array recursivly
     * Not only the top level elements in a multidimentional array
     *
     * @param mixed $array
     * @return integer
     */
    public function recursiveCount($array) : int
    {
        return (int) count($array, COUNT_RECURSIVE);
    }

    /**
     * indexes the provided Array by the index
     * Additionaliy you van provide a callable function 
     *
     * @param array $array
     * @param string $index
     * @param callable $callback
     * @param bool $returnObject = false
     * @param bool $skipIfMissing = false
     * @return array
     */
    public static function index(array $array, string $index, callable $callback = null, bool $returnObject = false, bool $skipIfMissing = false)
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
                    throw new \Exception('Specified Index not pressent in array element with key: ' . $index, 1);
                }
            }

            if (isset($tmp[$value[$index]])) {
                $tmp[$value[$index]][] = $value;
            } else {
                $tmp[$value[$index]] = $value;
            }
        }

        return ($returnObject) ? self::objectify($tmp) : self::sanitize($tmp);
    }


    /**
     * Maps the values of the $array to new keys
     * Adds support for multidimentional arrays
     * 
     * $map = [
     *      The KEY is the to be used key for the Array 
     *      'field' is the value origin
     *      'Name' => ['field' => 'name'],
     *      Add 'value' to add custom value or value mutation
     *      'Email' => ['field' => 'email', 'value' => 'Some Other Value'],
     *      Add '.' seperators for sub objects
     *      'Email.primary' => ['field' => 'email'],
     *      Use '[0]' for arrays
     *      'Addresses.[0].street' => ['field => 'address_1_line_1'],
     *      Get a value from a multidimentional source
     *      'isDefault' => ['field' => 'meta.system.default'],
     *
     * @param array $map
     * @param array $array
     * @return array
     * 
     * @todo add checks for keys and strict null allowing
     */
    public static function map(array $map, array $array, $strict = false)
    {
        $array = self::flatten($array);

        $tmp = [];
        foreach($map as $key => $item) {
            $tmp[$key] = (isset($item['value'])) ? $item['value'] : self::get($item['field'], $array); // $array[$item['field']];
        }

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

    public static function walk(array &$array, $callback, ...$parameters)
    {
        foreach($array as &$element) {
            $callback($element, ...$parameters);
        }
    }

}
