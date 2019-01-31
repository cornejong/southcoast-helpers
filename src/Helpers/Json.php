<?php

/*
 * Created on Wed Jan 30 2019
 *
 * Copyright (c) 2019 SouthCoast
 */


namespace SouthCoast\Helpers;

class Json
{
    public static function isValid(string $json) : bool
    {
        json_decode($json);

        if(JsonError::occurred()) {
            throw new JsonError();
        }

        return true;
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
}

class JsonError extends \Error
{

    const ERROR_MESSAGE = 'Invalid Json Provided! Error Message: ';

    public function __construct()
    {
        parent::__construct(self::ERROR_MESSAGE . json_last_error_msg(), json_last_error());
    }

    public static function occurred()
    {
        return (json_last_error() !== JSON_ERROR_NONE) ? true : false;
    }
}
