<?php

namespace SouthCoast\Helpers\Error;

use SouthCoast\Helpers\Json;

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

    const NOT_A_FILE = [
        'message' => 'The provided path is not a file! Provided: ',
        'code' => 850,
    ];

    const IDENTIFIER_ALREADY_IN_USE = [
        'message' => 'The provided identifier is already in use! Provided: ',
        'code' => 777,
    ];

    const UNKNOWN_DIRECTORY_IDENTIFIER = [
        'message' => 'The provided identifier is unknown! Provided: ',
        'code' => 770,
    ];

    const NEITHER_FILE_NOR_DIRECTORY = [
        'message' => 'The provided path leads to neither a file nor a directory! Provided: ',
        'code' => 550,
    ];

    const COULD_NOT_DELETE = [
        'message' => 'Could not delete the resource in the provided path! Provided: ',
        'code' => 660,
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
