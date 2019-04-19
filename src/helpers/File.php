<?php

namespace SouthCoast\Helpers;

class File
{
    const BASE_DIRECTORY = 'base_directory';
    const DIRECTORY_MAP_IDENTIFIER = '$';
    const NOTHING = '';

    /**
     * Holds the path to the base directory
     *
     * @var string
     */
    protected static $base_directory = null;

    /**
     * Holds the directory mapping
     *
     * @var array
     */
    protected static $directory_map = [];

    /**
     * Sets the base directory
     *
     * @param string $path
     */
    public static function setBaseDirectory(string $path)
    {
        /* Check if the path is valid */
        if (!Validate::path($path)) {
            throw new FileError(FileError::NOT_VALID_PATH, $path);
        }
        /* Check if the path leads to a directory */
        if (!Validate::isDirectory($path)) {
            throw new FileError(FileError::NOT_A_DIRECTORY, $path);
        }
        /* Define the directory */
        self::defineDirectory(File::BASE_DIRECTORY, $path);
    }

    /**
     * Lists all files and directories in the provided path
     *
     * @param string $path
     * @param string $extension
     * @param boolean $files_only
     * @param boolean $subtract_base_path
     * @return array
     * @throws FileError
     */
    function list(string $path, string $extension = null, bool $files_only = true, bool $subtract_base_path = true): array
    {
        /* First get the full path */
        $path = self::getPath($path);

        /* We can only list directories so check if this is one */
        if (!Validate::isDirectory($path)) {
            throw new FileError(FileError::NOT_A_DIRECTORY, $path);
        }

        /* Build the query for the file listing */
        if (is_null($extension)) {
            $query = $path . (($files_only) ? DIRECTORY_SEPARATOR . '*.*' : '');
        } else {
            $query = $path . DIRECTORY_SEPARATOR . '*.' . $extension;
        }

        /* Get the list */
        $list = glob($query);

        /* Check if there was anything found */
        if ($list === false) {
            /* if not, just return an empty array */
            return [];
        }

        /* Check if we need to clean up the paths */
        if ($subtract_base_path) {
            /* If so, strip the path from the list results */
            $list = self::stripEachBasePath($path, $list);
        }

        /* Finally return the list */
        return $list;
    }

    /**
     * Strips the base path from the full path
     *
     * @param string $path
     * @param array $list
     * @return array
     */
    public static function stripEachBasePath(string $path, array $list): array
    {
        /* Loop over the provided list */
        foreach ($list as &$subject) {
            /* Strip the base path from the item */
            $subject = self::stripBasePath($path, $subject);
        }

        /* return the list */
        return $list;
    }

    /**
     * Strips the base path from the full path
     *
     * @param string $base_path
     * @param string $subject
     */
    public static function stripBasePath(string $base_path, string $subject): string
    {
        /* replace the base path by nothing in the to be striped value */
        return str_replace($base_path . DIRECTORY_SEPARATOR, self::NOTHING, $subject);
    }

    /**
     * Checks if the directory identifier is known
     *
     * @param string $path
     * @return bool
     */
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

    /**
     * Extracts the directory identifier from the provided path
     *
     * @param string $path
     * @return string
     */
    public static function extractIdentifier(string $path): string
    {
        /* Get the first element in the provided path */
        $identifier = explode(DIRECTORY_SEPARATOR, $path)[0];
        /* Remove the identifier token */
        $identifier = ltrim($identifier, self::DIRECTORY_MAP_IDENTIFIER);
        /* return the identifier */
        return $identifier;
    }

    /**
     * Returns te full existing path to the file
     * Converts the identifier based paths into real paths
     *
     * @param string $path
     * @return string
     * @throws FileError
     */
    public static function getPath(string $path): string
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

    /**
     * Returns the real path to the identifier path
     *
     * @param string $identifier
     * @param string $path
     * @return string
     * @throws FileError
     */
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
        /* Build and return the new Path from the directory path and the provided path */
        return $directory_path . implode(DIRECTORY_SEPARATOR, $path_array);
    }

    /**
     * Checks if the identifier is known
     *
     * @param string $identifier
     * @return bool
     */
    public static function isKnownIdentifier(string $identifier): bool
    {
        return array_key_exists($identifier, self::$directory_map);
    }

    /**
     * Cleans up the path to get a consistent format
     *
     * @param string $path
     * @return string
     */
    public static function cleanUpPath(string $path): string
    {
        if (StringHelper::endsWith(DIRECTORY_SEPARATOR, $path)) {
            $path = rtrim($path, DIRECTORY_SEPARATOR);
        }

        return $path;
    }

    /**
     * Returns the path associated with the identifier
     *
     * @param string $identifier
     * @throws FileError
     */
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
     * @throws FileError
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
        /* Check if this is an already known identifier */
        if (self::isKnownIdentifier($identifier)) {
            throw new FileError(FileError::IDENTIFIER_ALREADY_IN_USE, $identifier);
        }
        /* If the identifier is the base directory */
        if ($identifier === File::BASE_DIRECTORY) {
            /* Add it to the base directory */
            self::$base_directory = $path;
        }
        /* Add it to the directory map */
        self::$directory_map[$identifier] = $path;
    }

    /**
     * Loads a directory map from array
     *
     * @param array $map
     * @return void
     */
    public static function loadDirectoryMap(array $map)
    {
        /* Loop over all the entries */
        foreach ($map as $identifier => $path) {
            /* Define each directory */
            self::defineDirectory($identifier, $path);
        }
    }

    /**
     * Removes all defined paths
     *
     * @param bool $removeBasePath
     * @return void
     */
    public static function clearDirectoryMap(bool $removeBasePath = false)
    {
        /* Save the base path */
        $base_directory_path = self::$directory_map[self::BASE_DIRECTORY];
        /* Empty the mapping */
        self::$directory_map = [];
        /* Check if we need to keep the base directory */
        if (!$removeBasePath) {
            /* Set the base path again */
            self::defineDirectory(self::BASE_DIRECTORY, $base_directory_path);
        }
    }

    /**
     * Recursively removed a directory.
     *
     * @param string $directory     The to-be removed directory
     * @return bool
     */
    private static function recursiveRemoveDirectory(string $directory)
    {
        /* Fist get the actual path */
        $directory = self::getPath($directory);

        /* If it's not a directory */
        if (!Validate::isDirectory($directory)) {
            return true;
        }

        /* Loop over all the items in the directory */
        foreach (self::list($directory, null, false, false) as $path) {
            /* Check if it's a directory */
            if (Validate::isDirectory($path)) {
                /* Recursively call this method */
                File::recursiveRemoveDirectory($path);
            } else {
                /* Else, unlink the file */
                @unlink($path);
            }
        }

        /* Finally remove the directory itself */
        @rmdir($directory);
    }

    /**
     * Performs a recursive listing.
     * Returns a list of all files in the provided directory and sub directories.
     *
     * @param string $directory     The to be scanned directory
     * @param string $pattern       The REGEX search pattern
     * @return array                The list of all directories and files.
     */
    protected static function recursiveList(string $directory, string $pattern = '/.*$/'): array
    {
        /* Get the real path to the directory */
        $path = self::getPath($directory);

        /* Create a new Recursive directory object */
        $recursive_directory = new RecursiveDirectoryIterator($path);
        /* Create a new Recursive Iterator object */
        $iterator_object = new RecursiveIteratorIterator($recursive_directory);
        /* Setup the filter pattern and perform it on all the files in the provided directory */
        $expression_result = new RegexIterator($iterator_object, $pattern, RegexIterator::GET_MATCH);

        /* Initialize the list variable */
        $list = [];

        /* Loop over all the directories */
        foreach ($expression_result as $files) {
            /* add the files to the file list */
            $list = array_merge($list, $files);
        }

        /* Garbage Collection */
        unset($recursive_directory, $iterator_object, $expression_result);

        /* Return the list */
        return $list;
    }

    /**
     * SETTERS
     */

    public static function get($path): string
    {
        # code...

        return '';
    }

    /**
     * @param string $path
     * @param bool $returnObject
     */
    public static function getJson(string $path, bool $returnObject = true)
    {
        return Json::parse(self::get($path), ($returnObject) ? false : true);
    }

    public static function getBasePath()
    {
        return self::getPathFromIdentifier(self::BASE_DIRECTORY);
    }

    /**
     * GETTERS
     */
}

class FileError extends \Error
{
    const NOT_VALID_PATH = [
        'message' => 'The provided path is not valid! Provided: ',
        'code' => 999,
    ];

    const NOT_A_DIRECTORY = [
        'message' => 'The provided path is not a directory! Provided: ',
        'code' => 888,
    ];

    const IDENTIFIER_ALREADY_IN_USE = [
        'message' => 'The provided identifier is already in use! Provided: ',
        'code' => 777,
    ];

    const UNKNOWN_DIRECTORY_IDENTIFIER = [
        'message' => 'The provided identifier is unknown! Provided: ',
        'code' => 770,
    ];

    /**
     * @param array $error
     * @param $extra
     */
    public function __construct(array $error, $extra = null)
    {
        extract($error);

        if (!empty($extra)) {
            $message .= "\n" . Json::prettyEncode($extra);
        }

        parent::__construct($message, $code);
    }
}
