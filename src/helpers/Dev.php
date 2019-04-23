<?php

namespace SouthCoast\Helpers;

use SouthCoast\Helpers\Env;
use \Exception;

class Dev
{
    /* CONSOLE COLOURS */
    /**
     * @var array
     */
    private static $foreground_colors = [];
    /**
     * @var array
     */
    private static $background_colors = [];

    /* ENVIROMENT */
    /**
     * @var mixed
     */
    private static $ENV_DEV;
    /**
     * @var mixed
     */
    private static $ENV_CONSOLE;

    /* TEMP DIRECOTRY */
    /**
     * @var mixed
     */
    private static $temp_directory;
    /**
     * @var string
     */
    private static $temp_extention = 'temp';

    /* LOG DATA TO FILE */
    /**
     * @var mixed
     */
    private static $LOG_TO_FILE = false;
    /**
     * @var mixed
     */
    private static $LOGBOOK_DIRECTORY;
    /**
     * @var string
     */
    private static $log_file_name = '';

    /* LOG DATA TO METHOD */
    /**
     * @var mixed
     */
    private static $LOG_TO_FUNCTION = false;
    /**
     * @var mixed
     */
    private static $LOG_FUNCTION_CALLBACK;
    /**
     * @var mixed
     */
    private static $LOG_FUNCTION_PARAMETERS;
    /**
     * @var mixed
     */
    private static $runtime_id;

    /**
     * @param $message
     * @param $die
     * @return null
     */
    public static function log($message, $die = false)
    {
        if (self::$LOG_TO_FILE) {
            self::saveLog(is_array($message) || is_object($message) ? print_r($message, true) : $message);
        }

        if (self::$LOG_TO_FUNCTION) {
            call_user_func(self::$LOG_FUNCTION_CALLBACK, $message, self::$runtime_id, ...self::$LOG_FUNCTION_PARAMETERS);
        }

        if (!self::isDev()) {
            return;
        }

        if (is_array($message) || is_object($message)) {
            print_r($message);
            if ($die) {
                die();
            }

            return;
        }

        switch ($message[0]) {
            /* New Line Prefix */
            case '>':
                $message = "\n" . $message;
                break;

            /* Tab Prefix */
            case '$':
                $message = "\t" . $message;
                break;

            /* Error Prefix */
            case 'X':
                $message = self::get_colored_string($message, 'red');
                break;

            /* Warning Prefix */
            case '*':
                $message = self::get_colored_string($message, 'yellow');
                break;

            case '-':
                $message = self::get_colored_string($message, 'white', 'blue');
                break;

            case '^':
                $message = self::get_colored_string($message, 'white', 'green');
                break;
        }

        print $message . "\n";

        if ($die) {
            die();
        }

        return;
    }

    protected static function isDev()
    {
        if (class_exists('\SouthCoast\Helpers\Env') && Env::isLoaded() && !isset(self::$ENV_DEV)) {
            self::$ENV_DEV = Env::isDev();
            self::$ENV_CONSOLE = Env::isConsole();
        } else {
            self::$ENV_CONSOLE = defined('STDIN');
        }

        return (self::$ENV_DEV || self::$ENV_CONSOLE) ? true : false;
    }

    /**
     * @param bool $isDev
     */
    public static function setDev(bool $isDev)
    {
        self::$ENV_DEV = $isDev;
    }

    /**
     * @param string $path
     * @param null $file_name
     */
    public static function logToFile(string $path = null, $file_name = 'LOG')
    {
        if (!is_null($path) && !file_exists($path)) {
            throw new \Exception('No Valid directory provided! Provided: ' . $path, 1);
        } elseif (is_null($path)) {
            $path = self::getTempDirectory();
        }

        self::$log_file_name = uniqid($file_name . '_');
        self::$LOG_TO_FILE = true;
        self::setLogbookDirectory($path);
    }

    /**
     * @param array $function
     * @param $parameters
     */
    public static function logToFunction(array $function, ...$parameters)
    {
        if (!method_exists(...$function)) {
            throw new \Exception('The Provided function doesn\'t exist! Provided: ' . $function, 1);
        }

        self::$LOG_TO_FUNCTION = true;
        self::$LOG_FUNCTION_CALLBACK = $function;
        self::$LOG_FUNCTION_PARAMETERS = $parameters;
        self::$runtime_id = Identifier::newGuid();
    }

    /**
     * @param string $path
     */
    public static function setTempDirectory(string $path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0700, true);
        }

        self::$temp_directory = $path;
    }

    /**
     * @param string $path
     */
    public static function setLogbookDirectory(string $path)
    {
        if (!file_exists($path)) {
            mkdir($path, 0700, true);
        }

        self::$LOGBOOK_DIRECTORY = $path;
    }

    public static function getTempDirectory()
    {
        if (!isset(self::$temp_directory)) {
            throw new \Exception('No Temporary directory provided!', 1);
        }

        return self::$temp_directory;
    }

    /**
     * @param string $ext
     */
    public static function setTempExtention(string $ext)
    {
        self::$temp_extention = $ext;
    }

    /**
     * @param $data
     * @param bool $die
     * @return null
     */
    public static function logJson($data, bool $die = false): void
    {
        if (!self::isDev()) {
            return;
        }

        self::log(Json::prettyEncode($data), $die);
    }

    /**
     * @param $data
     * @param bool $die
     */
    public static function varLog($data, bool $die = false)
    {
        ob_start();
        var_dump($data);
        $result = ob_get_clean();

        self::log($result, $die);
    }

    /**
     * @param $message
     * @param bool $die
     */
    public static function rainbowLog($message, bool $die = false)
    {
        $string = '';

        foreach (str_split($message) as $char) {
            $string .= self::get_colored_string($char, self::getRandomForgroundColor(), null);
        }

        self::log($string, $die);
    }

    /**
     * @param string $name
     * @param $data
     * @param $jsonEncode
     * @param falsebool $append
     */
    public static function store(string $name, $data, $jsonEncode = false, bool $append = false)
    {
        if (!isset(self::$temp_directory)) {
            throw new \Exception('No Temporary direcotry provided!', 1);
        }

        return file_put_contents(self::$temp_directory . DIRECTORY_SEPARATOR . $name . '.' . (($jsonEncode) ? 'json' : self::$temp_extention), ($jsonEncode) ? Json::prettyEncode($data) : $data, ($append) ? FILE_APPEND : null);
    }

    /**
     * @param bool $die
     * @param falsebool $asArray
     */
    public static function trace(bool $die = false, bool $asArray = false)
    {
        /* Check if we need to return an array with the trace */
        if ($asArray) {
            /* Return an array */
            return debug_backtrace();
        }
        /* Else, Print te trace */
        debug_print_backtrace();

        /* Check if we need to die */
        if ($die) {
            /* die */
            die();
        }
    }

    /**
     * Saves the log to a file
     *
     * @param [type] $data
     * @return void
     *
     */
    public static function saveLog($data)
    {
        $string = '[ ' . date('Y-m-d H:i:s') . ' ] ' . $data . "\n";
        $response = file_put_contents(self::$LOGBOOK_DIRECTORY . DIRECTORY_SEPARATOR . self::$log_file_name . '.txt', $string, FILE_APPEND);
        return $response != false ? true : false;
    }

    public static function getRandomForgroundColor()
    {
        if (!isset(self::$background_colors) || self::$foreground_colors) {
            self::setup_colors();
        }

        return array_keys(self::$foreground_colors)[rand(0, count(self::$foreground_colors) - 1)];
    }

    public static function getRandomBackgroundColor()
    {
        if (!isset(self::$background_colors) || self::$foreground_colors) {
            self::setup_colors();
        }

        return array_keys(self::$background_colors)[rand(0, count(self::$background_colors) - 1)];
    }

    /**
     * @param string $string
     * @param string $foreground_color
     * @param nullstring $background_color
     * @return mixed
     */
    public static function get_colored_string(string $string, string $foreground_color = null, string $background_color = null)
    {
        if (!isset(self::$background_colors) || self::$foreground_colors) {
            self::setup_colors();
        }

        $colored_string = "";

        // Check if given foreground color found
        if (isset(self::$foreground_colors[$foreground_color])) {
            $colored_string .= "\033[" . self::$foreground_colors[$foreground_color] . "m";
        }
        // Check if given background color found
        if (isset(self::$background_colors[$background_color])) {
            $colored_string .= "\033[" . self::$background_colors[$background_color] . "m";
        }

        // Add string and end coloring
        $colored_string .= $string . "\033[0m";

        return $colored_string;
    }

    /**
     * implements usleep with time in seconds
     *
     * @method wait
     * @param $time_in_seconds
     */
    public static function wait($time_in_seconds)
    {
        usleep($time_in_seconds * 1000000);
    }

    public static function setup_colors()
    {
        self::$foreground_colors['black'] = '0;30';
        self::$foreground_colors['dark_gray'] = '1;30';
        self::$foreground_colors['blue'] = '0;34';
        self::$foreground_colors['light_blue'] = '1;34';
        self::$foreground_colors['green'] = '0;32';
        self::$foreground_colors['light_green'] = '1;32';
        self::$foreground_colors['cyan'] = '0;36';
        self::$foreground_colors['light_cyan'] = '1;36';
        self::$foreground_colors['red'] = '0;31';
        self::$foreground_colors['light_red'] = '1;31';
        self::$foreground_colors['purple'] = '0;35';
        self::$foreground_colors['light_purple'] = '1;35';
        self::$foreground_colors['brown'] = '0;33';
        self::$foreground_colors['yellow'] = '1;33';
        self::$foreground_colors['light_gray'] = '0;37';
        self::$foreground_colors['white'] = '1;37';

        self::$background_colors['black'] = '40';
        self::$background_colors['red'] = '41';
        self::$background_colors['green'] = '42';
        self::$background_colors['yellow'] = '43';
        self::$background_colors['blue'] = '44';
        self::$background_colors['magenta'] = '45';
        self::$background_colors['cyan'] = '46';
        self::$background_colors['light_gray'] = '47';
    }

    public static function registerCustomExceptionHandler()
    {
        $handler = function ($th) {
            if ($th instanceof \Exception) {
                Dev::log('X EXCEPTION! == ' . $th->getMessage());
                Dev::log($th->getTraceAsString());
                throw new \Exception($th->getMessage(), $th->getCode());
            } elseif ($th instanceof \Error) {
                Dev::log('X ERROR! == ' . $th->getMessage());
                Dev::log($th->getTraceAsString());
                throw new \Error($th->getMessage(), $th->getCode());
            } else {
                Dev::log('X ' . strtoupper(get_class($th)) . '! == ' . $th->getMessage());
                Dev::log($th->getTraceAsString());
                $class = get_class($th);
                throw new $class($th->getMessage(), $th->getCode());
            }
        };

        set_exception_handler($handler);

        set_error_handler($handler);
    }

    public static function restoreExceptionHanler()
    {
        restore_exception_handler();
        restore_error_handler();
    }
}
