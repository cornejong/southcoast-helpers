<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require realpath('../vendor/autoload.php');

use SouthCoast\Helpers\File;

File::setBaseDirectory(__DIR__);
// File::defineDirectory('dir_identifier', '/Path/To/Dir');

var_dump(File::getCsv('test.csv', false, ';'));

die();

File::list('$dir_identifier');

File::list(File::BASE_DIRECTORY, 'txt');

/* Save file to defined directory */
File::save('$dir_identifier/thisname.tmp', $data);

File::get('$dir_identifier/thisname.tmp');

/* Save file to base directory as Json */
File::saveJson('thisname', $data, File::Minified);

File::getJson('thisname', true);

File::saveCsv('thiscsv', $data, true || []);

File::getCsv('thiscsv', false);

/* Open a Write Stream to a file */
$stream = File::writeStream('anothername.txt');
/* Loop over all lines in a file */
foreach (File::getAsArray('thisname.txt') as $line) {
    $stream->write($line);
}
/* Close the stream */
$stream->close();

/* Open a Write Stream to a file */
$stream = File::writeStream('anothername.txt');

while ($stream->hasContent()) {
    /* Optionally you can pass the amount of bites */
    processContent($stream->read());
}

/* Close the stream, or just unset it */
$stream->close();
unset($stream);

/* Move a file to another directory */
File::move('$dir_identifier/thisname.tmp', '$another_defined_dir');
/* Rename a file */
File::rename('$dir_identifier/thisname.tmp', 'thisnewname');

/* Get File Extention */
File::getExtension('thisname.txt');
/* Get mime type */
File::getMimeType('thisname');

File::delete('thisname');

File::describe('thisname');

File::getDirectories();

File::getPath('thisname.json');
