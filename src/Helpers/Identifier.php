<?php

/*
 * Created on Wed Jan 30 2019
 *
 * Copyright (c) 2019 SouthCoast
 */


namespace SouthCoast\Helpers;

abstract class Identifier
{
    final public static function newGuid() : string
    {
        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    }

    final public static function newUniqId(string $prefix = null) : string
    {
        return uniqid((!is_null($prefix)) ? $prefix : '');
    }
}

