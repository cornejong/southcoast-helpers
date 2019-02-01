<?php

/*
 * Created on Thu Jan 31 2019
 *
 * Copyright (c) 2019 SouthCoast
 */


namespace SouthCoast\Helpers;

/**
 * A toolset for working with Environments in php
 * @author SouthCoast <hello@southcoast.tech>
 * @category Helper
 * @version 1.0.0
 * @package SouthCoast\Helpers
 */
abstract class Env 
{
    const FILENAME = 'sc.env';

    const REQUIRED_ENV_VALUES = [
        'dev' => 'Bool | Is this a development environments',
        'base_dir' => 'The path to the base direcotry of the application',
        'temp_dir' => 'Path | The path to the temp directory',
        'cache_dir' => 'Path | The path to the caching direcotry',
    ];

    protected static $enviroment;

    public static function load(string $path) : bool
    {
        if(!is_readable($path)) {
            throw new EnvError(EnvError::NON_EXISTING_ENV_FILE, $path);
        }

        $handle = fopen($path, 'r+');

        if(!$handle) {
            throw new EnvError(EnvError::COULD_NOT_OPEN_STREAM, $path);
        }

        $contents = '';

         while (!feof($handle)) {
            $content .= fread($handle, 8192);
        }

        fclose($handle);

        $content_array = Json::parseToArray($content);

        if(ArrayHelper::requiredPramatersAreSet(Env::REQUIRED_ENV_VALUES, $content_array, $missing, true)) {
            throw new EnvError(EnvError::MISSING_REQUIRED_ENV_VALUES, $missing);
        }

        self::$enviroment = $content_array;

        return true;
    }

    public static function __get(string $name)
    {
        return self::isset($name) ? self::$enviroment[$name] : void;
    }

    public static function __set(string $name, $value) : void
    {
        if(self::isset($name)) {
            throw new EnvError(EnvError::OVERRIDE_PROTECTION);
        }

        self::$enviroment[$name] = $value;
    }

    public static function __isset(string $name) : bool
    {
        return isset(self::$enviroment[$name]);
    }

    public static function isDev() : bool
    {
        return self::$enviroment['dev'];
    }

    public static function isConsole() : bool
    {
        return (defined('STDIN')) ? true : false;
    }
}

class EnvError extends \Error
{

    const OVERRIDE_PROTECTION = [
        'message' => 'Overriding of existing Enviroment data is not allowd!',
        "code" => 999
    ];

    const NON_EXISTING_ENV_FILE = [
        'message' => 'The provided path to the ' . Env::FILENAME . ' file is not reachable!! Path: ',
        'code' => 12
    ];

    const COULD_NOT_OPEN_STREAM = [
        'message' => 'Could not open stream to the ' . Env::FILENAME . ' file! Path: ',
        'code' => 15
    ];

    const MISSING_REQUIRED_ENV_VALUES = [
        'message' => 'There are required elements missing from your env file! Missing: ',
        'code' => 15
    ];




    public function __construct($error, $extra = null)
    {
        extract($error);
        
        if(!empty($extra)) {
            $message .= "\n" . Json::prettyEncode($extra);
        }

        parent::__construct($message, $code);
    }
}

