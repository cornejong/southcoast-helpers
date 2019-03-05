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
        'machine' => 'String | The Identifier for the current runtime. (development machine, developer name)',
        'base_dir' => 'Path | The path to the base direcotry of the application',
        'temp_dir' => 'Path | The path to the temp directory',
        'cache_dir' => 'Path | The path to the caching direcotry',
    ];

    protected static $enviroment = [];
    protected static $env_path = '';

    public static function load(string $path, bool $reload = false) : bool
    {
        if (!is_readable($path)) {
            throw new EnvError(EnvError::NON_EXISTING_ENV_FILE, $path);
        }

        self::$env_path = $path;

        $handle = fopen($path, 'r+');

        if (!$handle) {
            throw new EnvError(EnvError::COULD_NOT_OPEN_STREAM, $path);
        }

        $content = '';

        while (!feof($handle)) {
            $content .= fread($handle, 8192);
        }

        fclose($handle);

        $content_array = Json::parseToArray($content);

        if (ArrayHelper::requiredPramatersAreSet(Env::REQUIRED_ENV_VALUES, $content_array, $missing, true)) {
            throw new EnvError(EnvError::MISSING_REQUIRED_ENV_VALUES, $missing);
        }

        self::$enviroment = $content_array;

        if (!$reload) {
            self::defineConstants(self::$enviroment);
        }

        // self::defineVariables(self::$enviroment);

        return true;
    }

    public static function reload()
    {
        if (!isset(self::$env_path)) {
            throw new EnvError(EnvError::NON_EXISTING_ENV_FILE, self::$env_path);
        }

        self::load(self::$env_path, true);
    }

    public static function defineVariables(array $enviroment)
    {
        foreach ($enviroment as $name => $value) {
            try {
                self::${$name} = $value;
            } catch (\Throwable $th) {
                throw new EnvError(EnvError::EXCEPTION_THROWN, ['Message' => $th->getMessage(), 'trace' => $th->getTrace()]);
            }
        }
    }

    public static function defineConstants(array $enviroment)
    {
        foreach ($enviroment as $name => $value) {
            if (!defined($name)) {
                throw new EnvError(EnvError::ENV_CONST_ALREADY_DEFINED, [$name => $value]);
            }

            try {
                define($name, $value);
            } catch (\Throwable $th) {
                throw new EnvError(EnvError::EXCEPTION_THROWN, ['Message' => $th->getMessage(), 'trace' => $th->getTrace()]);
            }
        }
    }

    public static function simulateProduction()
    {
        # code...
    }

    public function __get(string $name)
    {
        return self::isset($name) ? self::$enviroment[$name] : void;
    }

    public function __set(string $name, $value)
    {
        if (self::isset($name)) {
            throw new EnvError(EnvError::OVERRIDE_PROTECTION);
        }

        self::$enviroment[$name] = $value;
    }

    public function __isset(string $name) : bool
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

    public static function inConsole() : bool
    {
        return self::isConsole();
    }

    public static function isLoaded() : bool
    {
        return !empty(self::$enviroment) ? true : false;
    }


    static public function __callStatic($method, $args)
    {
        if (preg_match('/^([gs]et)([A-Z])(.*)$/', $method, $match)) {
            switch ($match[1]) {
                case 'set':
                    self::$enviroment[$args[0]] = $args[1];
                    break;

                case 'get':
                    return self::$enviroment[$args[0]];
                    break;
            }
        } else {
            throw new InvalidArgumentException("Property {$args[0]} doesn't exist.");
        }
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
        'code' => 16
    ];

    const ENV_CONST_ALREADY_DEFINED = [
        'message' => 'One or more enviroment variables set in ' . Env::FILENAME . ' are already defined! Variable: ',
        'code' => 20
    ];

    const EXCEPTION_THROWN = [
        'message' => 'There was an Exception or Error Thrown! Message: ',
        'code' => 000
    ];

    public function __construct(array $error, $extra = null)
    {
        extract($error);

        if (!empty($extra)) {
            $message .= "\n" . Json::prettyEncode($extra);
        }

        parent::__construct($message, $code);
    }
}

