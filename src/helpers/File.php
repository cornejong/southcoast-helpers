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
    public static function isKnownDirectory(string $path): bool
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
            /* get the real path to the directory */
            $path = self::getRealPath($path);
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
     * Example:
     *      File::getRealPath('cache/some_cached_file.tmp') = '/path/to/cache/directory/some_cached_file.tmp';
     *      File::getRealPath('cache') = '/path/to/cache/directory';
     *
     * @param string $path              The file or directory path based on the identifier
     * @return string                   The full actual path to the file or directory
     * @throws FileError                If: UNKNOWN_DIRECTORY_IDENTIFIER
     */
    public static function getRealPath(string $path): string
    {
        /* Check if this is a known identifier */
        if (!self::isKnownIdentifier($identifier)) {
            throw new FileError(FileError::UNKNOWN_DIRECTORY_IDENTIFIER, $identifier);
        }

        /* get the identifier */
        $identifier = self::extractIdentifier($path);
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
     * Example:
     *      File::cleanUpPath('/foo/bar/foobar/') = '/foo/bar/foobar';
     *
     * @param string $path              The to-be cleaned path
     * @return string                   The cleaned path
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
     * @param string $identifier        The directory identifier
     * @return string                   The full path to the directory
     * @throws FileError                If: UNKNOWN_DIRECTORY_IDENTIFIER
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
     * Example:
     *      File::defineDirectory('cache', '/the/full/path/to/the/dir');
     *
     * @param string $identifier        The directory identifier
     * @param string $path              The full path to the directory
     * @return void
     * @throws FileError                If: NOT_VALID_PATH, NOT_A_DIRECTORY, IDENTIFIER_ALREADY_IN_USE
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
        return @rmdir($directory);
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
     * Get the extension of a file
     *
     * Example:
     *      File::getExtension('$base_directory/myAwesomeFile.txt') = 'txt';
     *      File::getExtension('$cache_directory/some_cached_file') = null;
     *
     * @param string $file          The file for which you want the extension
     * @return string|null          Returns a string with the extension, null if non found
     */
    public function getExtension(string $file)
    {
        /* Get the real path to the directory */
        $path = self::getPath($file);
        /* Explode the file's basename by the '.' */
        $file_name_array = explode('.', basename($oath));
        /* Check if there are more than 1 items in the array */
        if (count($file_name_array) === 1) {
            /* If there aren't, there is no extension, so return null */
            return null;
        }
        /* return the last item in the exploded array */
        return $file_name_array[array_key_last($file_name_array)];
    }

    /**
     * Change the extension of an existing file
     *
     * Example:
     *      File::changeExtension('$base_directory/LOG_1234.log', 'txt');
     *
     * @param string $file              The to be changed file
     * @param string $new_extension     The new file extension
     * @return bool                     Returns true is all went good, false if not
     */
    public function changeExtension(string $file, string $new_extension): bool
    {
        /* Get the real path to the directory */
        $path = self::getPath($file);
        /* Get the file extension */
        $extension = self::getExtension($path);
        /* Add the new extension */
        $new_path = (!is_null($extension) ? rtrim($path, $extension) : $path . '.') . $new_extension;
        /* rename the file to reflect the new extension and return the result */
        return rename($path, $new_path);
    }

    /**
     * Rename an existing file
     *
     * Example:
     *      File::rename('$base_directory/myAwesomeFile.md', 'myNotSoAwesomeFile');
     *
     * @param string $file              The to-be renamed file
     * @param string $new_name          The new file name (no extension)
     * @return bool                     Returns true is all went good, false if not
     */
    public static function rename(string $file, string $new_name): bool
    {
        /* Get the real path to the directory */
        $path = self::getPath($file);
        /* Get the file extension */
        $extension = self::getExtension($path);
        /* Get the file name, without the extension */
        $file_name = basename($oath, $extension ? '.' . $extension : null);
        /* Get the base path to the file */
        $base_path = str_replace(basename($path), '', $path);
        /* Rename the file and return the result */
        return rename($path, $base_path . $new_name . ($extension ? '.' . $extension : ''));
    }

    /**
     * Move an existing file to a new location
     *
     * Example:
     *      File::move('$base_directory/myAwesomeFile.txt', '$some_other_directory');
     *
     * @param string $file              The to-be moved file
     * @param string $new_location      The new directory (no file name should be present)
     * @return bool                     Returns true is all went good, false if not
     */
    public static function move(string $file, string $new_location): bool
    {
        /* Get the real path to the original directory */
        $path = self::getPath($file);
        /* Get the real path to the new directory and append the filename */
        $new_path = self::getPath($new_location) . basename($oath);
        /* move the file to the new location and return the result */
        return rename($path, $new_path);
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
