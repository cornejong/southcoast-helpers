<?php


namespace SouthCoast\Helpers;

class File
{
    const BaseDirectory = 'base_directory';
    const DIRECTORY_MAP_IDENTIFIER = '$';
    const NOTHING = '';

    protected static $base_directory = null;
    protected static $directory_map = [];


    public static function setBaseDirectory(string $path)
    {
        if (!Validate::path($path)) {
            throw new FileError(FileError::NOT_VALID_PATH, $path);
        }

        if (!Validate::isDirectory($path)) {
            throw new FileError(FileError::NOT_A_DIRECTORY, $path);
        }

        self::defineDirectory('base_directory', $path);
    }

    public static function list(string $path, bool $files_only = true, string $extention = null, bool $subtract_base_path = true): array
    {
        $path = self::cleanUpPath($path);

        if (self::isKnownDirectory($path)) {
            $path = self::getPathFromIdentifier(self::extractIdentifier($path));
        }

        if (!Validate::path($path)) {
            throw new FileError(FileError::NOT_VALID_PATH, $path);
        }

        if (!Validate::isDirectory($path)) {
            throw new FileError(FileError::NOT_A_DIRECTORY, $path);
        }

        if (is_null($extention)) {
            $list = glob($path . (($files_only) ? DIRECTORY_SEPARATOR . '*.*' : ''));
        } else {
            $list = glob($path . DIRECTORY_SEPARATOR . '*.' . $extention);
        }

        if ($subtract_base_path) {
            $list = self::stripEachBasePath($path, $list);
        }

        return $list;
    }

    public static function stripEachBasePath(string $path, array $list): array
    {
        /* Loop over the provided list */
        foreach ($list as &$item) {
            /* Strip the base path from the item */
            $item = self::stripBasePath($path, $item);
        }
        /* return the list */
        return $list;
    }

    public static function stripBasePath(string $base_path, string $to_strip): string
    {
        /* replace the base path by nothing in the to be striped value */
        return str_replace($base_path . DIRECTORY_SEPARATOR, self::NOTHING, $to_strip);
    }

    public static function isKnownDirectory(string &$path): bool
    {
        /* Check if the path starts with the identifier token */
        if (!StringHelper::startsWith(self::DIRECTORY_MAP_IDENTIFIER, $path)) {
            /* if not, simple, its not. return false */
            return false;
        }
        /* Extract the identifier from the path */
        $identifier = self::extractIdentifier($path);
        /* Check if the identifier is in the directory map, return a boolean */
        return array_key_exists($identifier, self::$directory_map) ? true : false;
    }

    public static function extractIdentifier(string $path): string
    {
        /* Get the first element in the provided path */
        $identifier = explode(DIRECTORY_SEPARATOR, $path)[0];
        /* Remove the identifier token */
        $identifier = ltrim($identifier, self::DIRECTORY_MAP_IDENTIFIER);
        /* return the identifier */
        return $identifier;
    }

    public static function getFilePath(string $path): string
    {
        /* Check if this is a predefined directory */
        if (self::isKnownDirectory($path)) {
            /* get the identifier */
            $identifier = self::extractIdentifier($path);
            /* get the real path to the directory */
            $path = self::getRealPath($identifier, $path);
        }
        /* Check if this is a valid path */
        if (!Validate::path($path)) {
            throw new FileError(FileError::NOT_VALID_PATH, $path);
        }
        /* return the path */
        return $path;
    }

    public static function getRealPath(string $identifier, string $path): string
    {
        /* Check if this is a known identifier */
        if (!self::isKnownIdentifier($identifier)) {
            throw new FileError(FileError::UNKNOWN_DIRECTORY_IDENTIFIER, $identifier);
        }

        /* Get the directory path from the identifier */
        $directory_path = self::getPathFromIdentifier($identifier);

        /* Explode the provided path */
        $path_array = explode(DIRECTORY_SEPARATOR, $path);
        /* Remove the Identifier */
        array_shift($path_array);
        /* Build and return the new Path from the direcotry path and the provided path */
        return $directory_path . implode(DIRECTORY_SEPERATOR, $path_array);
    }

    public static function isKnownIdentifier(string $identifier)
    {
        return array_key_exists($identifier, self::$directory_map);
    }

    public static function cleanUpPath(string $path): string
    {
        if (StringHelper::endsWith(DIRECTORY_SEPARATOR, $path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR);
        }

        return $path;
    }

    public static function getPathFromIdentifier(string $identifier): string
    {
        /* Lets make sure its a known directory */
        if (!self::isKnownIdentifier($identifier)) {
            throw new FileError(FileError::UNKNOWN_DIRECTORY_IDENTIFIER, $identifier);
        }
        /* Get the path associated with this identifier */
        return self::$directory_map[$identifier];
    }

    /**
     * Defines a directory by its identifier
     *
     * @param string $identifier
     * @param string $path
     * @return void
     */
    public static function defineDirectory(string $identifier, string $path)
    {
        /* First clean up the path */
        $path = self::cleanUpPath($path);
        /* Check if the path is valid */
        if (!Validate::path($path)) {
            throw new FileError(FileError::NOT_VALID_PATH, $path);
        }
        /* Check if the path is a directory */
        if (!Validate::isDirectory($path)) {
            throw new FileError(FileError::NOT_A_DIRECTORY, $path);
        }
        /* Check if this is an alreay known identifier */
        if (self::isKnownIdentifier($identifier)) {
            throw new FileError(FileError::IDENTIFIER_ALREADY_IN_USE, $identifier);
        }
        /* If the identifier is the base directory */
        if ($identifier == 'base_directory') {
            /* Add it to the base directory */
            self::$base_directory = $path;
        }
        /* Add it to the directory map */
        self::$directory_map[$identifier] = $path;
    }

    public static function loadDirectoryMap(array $map)
    {
        /* Loop over all the entries */
        foreach ($map as $identifier => $path) {
            /* Define each directory */
            self::defineDirectory($identifier, $path);
        }
    }

    public static function clearDirectoryMap(bool $removeBasePath = false)
    {
        /* Save the base path */
        $base_directory_path = self::$directory_map[self::BaseDirectory];
        /* Empty the mapping */
        self::$directory_map = [];
        /* Check if the base path needed to be saved or not*/
        if ($removeBasePath) {
            /* Set the base path again */
            self::defineDirectory(self::BaseDirectory, $base_directory_path);
        }
    }


    /** 
     * SETTERS
     */

    public static function get($path): string
    {
        # code...

        return '';
    }

    public static function getJson(string $path, bool $returnObject = true)
    {
        return Json::parse(self::get($path), ($returnObject) ? false : true);
    }

    public static function getBasePath()
    {
        return self::getPathFromIdentifier(self::BaseDirectory);
    }



    /**
     * GETTERS
     */
}


class FileError extends \Error
{

    const NOT_VALID_PATH = [
        'message' => 'The provided path is not valid! Provided: ',
        'code' => 999
    ];

    const NOT_A_DIRECTORY = [
        'message' => 'The provided path is not a directory! Provided: ',
        'code' => 888
    ];

    const IDENTIFIER_ALREADY_IN_USE = [
        'message' => 'The provided identifier is already in use! Provided: ',
        'code' => 777
    ];

    const UNKNOWN_DIRECTORY_IDENTIFIER = [
        'message' => 'The provided identifier is unknown! Provided: ',
        'code' => 770
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
