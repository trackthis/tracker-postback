<?php

// Load composer
require implode(DIRECTORY_SEPARATOR, array(
    __DIR__,
    '..',
    'vendor',
    'autoload.php',
));

// Definitions
define('DS', DIRECTORY_SEPARATOR);
define('APPROOT', rtrim(dirname(__DIR__),DS));

// Keep the code somewhat short
use \Finwo\Framework\Config\Config;

// Initialize router
$router = new Klein\Klein();

// Initialize middleware


//// Initialize services
//$services = array();
//foreach( Config::get('services') as $name => $serviceClass ) {
//    if(!class_exists($serviceClass)) continue;
//    $service = new $serviceClass();
//    if(!($service instanceof AbstractService)) continue;
//    $services[$name] = $service;
//}
//Config::set('service', $services);



//// Initialize registered bundles
//$bundles = array();
//foreach( Config::get('bundles') as $bundleName ) {
//    $class = $bundleName . "\\" . @array_pop(explode("\\",$bundleName));
//    if(!class_exists($class)) continue;
//    $bundle = new $class($router);
//    if (!($bundle instanceof AbstractBundle)) continue;
//    array_push($bundles, new $class($router));
//}
//Config::set( 'bundles', $bundles );

// Handle pre-forked start
if(isset($_SERVER['argc'])) {
    require(APPROOT.DS.'init.php');
}

// Kickoff the request
$router->dispatch();
