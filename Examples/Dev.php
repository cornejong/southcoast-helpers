<?php
require '../src/helpers/Dev.php';

use SouthCoast\Helpers\Dev;

Dev::setDev(true);

Dev::log('Normal log');
Dev::log('> Seperated log');
Dev::log('$ Tab indented log');
Dev::log('X Some Error Log! Sh*t hit the fan!');
Dev::log('- A notification! Please notice me :)');
Dev::log('* Warming! Lets get to it!');

Dev::log('^ Check this out!');