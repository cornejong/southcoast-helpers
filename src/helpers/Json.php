<?php

/*
 * Created on Wed Jan 30 2019
 *
 * Copyright (c) 2019 SouthCoast
 */


namespace SouthCoast\Helpers;

class Json
{
    const PRETTY = JSON_PRETTY_PRINT;

    public static function isValid(string $json) : bool
    {
        json_decode($json);

        if(JsonError::occurred()) {
            throw new JsonError();
        }

        return true;
    }

    public static function stringify($data) : string
    {
        $json = json_encode($data);
        
        if(JsonError::occurred()) {
            throw new JsonError();
        }

        return $json;
    }

    public static function encode($data) : string
    {
        return self::stringify($data);
    }

    public static function parse(string $string, $array = false)
    {
        return ($array) ? self::parseToArray($string) : self::parseToObject($string);
    }

    public static function parseToObject(string $data)
    {
        if(!self::isValid($data)) {
            return false;
        }

        return json_decode($data);
    }

    public static function parseToArray(string $data)
    {
        if(!self::isValid($data)) {
            return false;
        }

        return json_decode($data, true);
    }

    public static function prettyEncode($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function add(array $elements, string $json) : string
    {
        $array = self::parse($json, true);
        foreach($elements as $key => $value) {
            if(isset($array[$key])) {
                throw new JsonError(JsonError::KEY_ALREADY_EXISTS, $key);
            }

            $array[$key] = $value;
        }

        return self::stringify($array);
    }

    public static function unset(string &$json, ...$keys)
    {
        $array = self::parse($json, true);
        
        foreach($keys as $key) {
            if(isset($array[$key])) {
                unset($array[$key]);
            } else {
                throw new JsonError(JsonError::KEY_DOES_NOT_EXIST, $key);
            }
        }

        $json = self::stringify($array);
    }

    public static function merge(...$strings) : string
    {
        $tmp = [];
        foreach($strings as $string) {
            $tmp = array_merge_recursive($tmp, self::parse($string, true));
        }
        return self::stringify($tmp);
    }
    
}

class JsonError extends \Error
{
    const LAST_JSON_ERROR = [];
    const KEY_ALREADY_EXISTS = [
        'message' => '',
        'code' => 123
    ];

    const ERROR_MESSAGE = 'Invalid Json Provided! Error Message: ';

    public function __construct($message = null, $extra = null)
    {
        parent::__construct(self::ERROR_MESSAGE . json_last_error_msg(), json_last_error());
    }

    public static function occurred()
    {
        return (json_last_error() !== JSON_ERROR_NONE) ? true : false;
    }
}
