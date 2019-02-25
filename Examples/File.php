<?php

File::setBaseDirectory(TEMP_FOLDER);
File::defineDirectory('dir_identifier', '/Path/To/Dir', 'index_23');

File::list();
File::list('$dir_identifier');

File::list(File::BaseDirectory, 'txt');

/* Save file to defined directory */
File::save('$dir_identifier/thisname.tmp', $data);

File::get('$dir_identifier/thisname.tmp');

/* Save file to base directory as Json */
File::saveJson('thisname', $data, File::Minified);

File::getJson('thisname', true);

File::saveCsv('thiscsv', $data, true||[]);

File::getCsv('thiscsv', true);

/* Open a Write Stream to a file */
$stream = File::writeStream('anothername.txt');
/* Loop over all lines in a file */
foreach(File::readStream('thisname') as $line) {
    $stream->write($line);
}
/* Close the stream */
$stream->close();

/* Move a file to another directory */
File::move('$dir_identifier/thisname.tmp', '$another_defined_dir');
/* Rename a file */
File::rename('$dir_identifier/thisname.tmp', 'thisnewname');

/* Get File Extention */
File::getExtention('thisname');
/* Get mime type */
File::getMimeType('thisname');

File::delete('thisname');

File::describe('thisname');

File::getDirectories();

File::getPath('thisname.json');
