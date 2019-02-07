<?php

namespace SouthCoast\Helpers;

use \Exception;

class Dev
{

    private static $foreground_colors = null;
    private static $background_colors = null;

    private static $ENV_DEV;
    private static $ENV_CONSOLE;

    private static $temp_directory;
    private static $temp_extention = 'temp';

    public static function log($message, $die = false) : void
    {
        if (!self::isDev()) return;

        if (is_array($message) || is_object($message)) {
            print_r($message);
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
 

        if ($die) die();
    }

    protected static function isDev()
    {
        if(class_exists('SouthCoast\\Helpers\\Env') && \SouthCoast\Helpers\Env::isLoaded() && !isset(self::$ENV_DEV)) {
            self::$ENV_DEV = Env::isDev();
            self::$ENV_CONSOLE = Env::isConsole();
        } else {
            self::$ENV_CONSOLE = defined('STDIN');
        }

        return (self::$ENV_DEV || self::$ENV_CONSOLE) ? true : false;
    }

    public static function setDev(bool $isDev)
    {
        self::$ENV_DEV = $isDev;
    }

    public static function setTempDirectory(string $path)
    {
        if(!file_exists($path)) {
            mkdir($path, 0700, true);
        }

        self::$temp_directory = $path;
    }


    public static function setTempExtention(string $ext)
    {
        self::$temp_extention = $ext;
    }

    public static function logJson($data, bool $die = false) : void
    {
        if (!self::isDev()) return;

        self::log(Json::prettyEncode($data), $die);
    }

    public static function varLog($data, bool $die = false)
    {
        ob_start();
        var_dump($data);
        $result = ob_get_clean();

        self::log($result, $die);
    }

    public static function rainbowLog($message, bool $die = false)
    {
        foreach(str_split($message) as $char) {
            $string .= self::get_colored_string($char, self::getRandomForgroundColor(), null);
        }
        self::log($string, $die);
    }


    public static function store(string $name, $data, $jsonEncode = false) 
    {
        if(!isset(self::$temp_directory)) {
            throw new \Exception('No Temporary direcotry provided!', 1);
        }

        return (file_put_contents(self::$temp_directory . DIRECTORY_SEPARATOR . $name . '.' . self::$temp_extention, ($jsonEncode) ? Json::prettyEncode($data) : $data));
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
    public static function wait($time_in_seconds) : void
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
}