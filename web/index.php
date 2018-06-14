<?php

// Load composer
require implode(DIRECTORY_SEPARATOR, array(
    __DIR__,
    '..',
    'vendor',
    'autoload.php',
));

// Definitions
if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
if(!defined('APPROOT')) define('APPROOT', rtrim(dirname(__DIR__),DS));

// Handle pre-forked start
if(isset($_SERVER['argc'])) {
    require(APPROOT.DS.'init.php');
}

// Keep the code somewhat short
use \Finwo\Framework\Config\Config;

// Initialize router
$GLOBALS['router'] = new Klein\Klein();

// Initialize middleware
foreach(glob(APPROOT.DS.'middleware'.DS.'**'.DS.'*.php') as $filename) {
    require_once($filename);
}

// Initialize controllers
foreach(glob(APPROOT.DS.'controller'.DS.'**'.DS.'*.php') as $filename) {
    require_once($filename);
}

// Kickoff the request
$GLOBALS['router']->dispatch();
