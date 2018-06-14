<?php

// Load composer
require implode(DIRECTORY_SEPARATOR, array(
    __DIR__,
    '..',
    'vendor',
    'autoload.php',
));

// Definitions
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('APPROOT')) {
    define('APPROOT', rtrim(dirname(__DIR__), DS));
}

// Handle pre-forked start
if (isset($_SERVER['argc'])) {
    require(APPROOT . DS . 'init.php');
}

// Initialize service handler
$_SERVICE = new Pimple\Container();

// Initialize services
foreach (array_merge(
             glob(APPROOT . DS . 'service' . DS . '*.php'),
             glob(APPROOT . DS . 'service' . DS . '**' . DS . '*.php')
         ) as $filename
) {
    require_once($filename);
}

// Initialize router
$GLOBALS['router'] = new Klein\Klein();

// Initialize middleware
foreach (array_merge(
             glob(APPROOT . DS . 'middleware' . DS . '*.php'),
             glob(APPROOT . DS . 'middleware' . DS . '**' . DS . '*.php')
         ) as $filename
) {
    require_once($filename);
}

// Initialize controllers
foreach (array_merge(
             glob(APPROOT . DS . 'controller' . DS . '*.php'),
             glob(APPROOT . DS . 'controller' . DS . '**' . DS . '*.php')
         ) as $filename
) {
    require_once($filename);
}

// Kickoff the request
$GLOBALS['router']->dispatch();
