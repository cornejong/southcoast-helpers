<?php

error_reporting(E_ALL);
ini_set('display_errors', true);

require '../src/helpers/Dev.php';

use SouthCoast\Helpers\Dev;

Dev::setDev(true);

Dev::log('X === Some Error Occured!');