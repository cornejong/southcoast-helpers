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
    public static function objectify(array $array) : StdClass
    {
        return self::sanitize($data, false);
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

        $dove = self::arrayDive(explode('.', $found), $array);

        return self::cleanupRebuild($dove);
    }


    public function requiredPramatersAreSet(array $parameters, array $data, &$missing, bool $strict = false) : bool
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
        $index = array_shift($keys);
        
        if(!isset($keys[0])) {
            $array[$index] = $value;
        } else {   
            $array[$index] = self::makeArray($keys,$value);    
        }
        
        return $array;
    }
    
    
    private static function rebuildArray(array $map) : array
    {
        $array = [];
    
        /* TODO: ADD SUPPORT FOR NEW APPENDING ARRAY ELEMENTS '[]' */
    
        foreach($map as $key => $value) {
            $key_array = explode('.', $key);
            if(count($key_array) == 1) {
                $array[$key] = $value;
            } else {
                $value = self::makeArray($key_array, $value);
                $array = array_merge_recursive($array, $value);
            }
        }
        
        return self::cleanupRebuild($array);
    }

    public static function cleanupRebuild(array $array) : array
    {
        return json_decode(preg_replace('/(\[|\])/', '', json_encode($array)), true);
    }

    public static function isAssoc(array $array)
    {
        if ([] === $arr) {
            return false;
        }

        return array_keys($array) !== range(0, count($array) - 1);
    }

    public function recursiceCount(\Countable $array) : int
    {
        return (int) count($array, COUNT_RECURSIVE);
    }

}
