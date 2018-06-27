<?php

// Load composer
require implode(DIRECTORY_SEPARATOR, array(
    __DIR__,
    '..',
    'vendor',
    'autoload.php',
));

// Catch pre-forked start
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
    require($filename);
}

// Initialize router
$GLOBALS['router'] = new Klein\Klein();

// Initialize middleware
foreach (array_merge(
             glob(APPROOT . DS . 'middleware' . DS . '*.php'),
             glob(APPROOT . DS . 'middleware' . DS . '**' . DS . '*.php')
         ) as $filename
) {
    require($filename);
}

// Initialize controllers
foreach (array_merge(
             glob(APPROOT . DS . 'controller' . DS . '*.php'),
             glob(APPROOT . DS . 'controller' . DS . '**' . DS . '*.php'),
             glob(APPROOT . DS . 'controller' . DS . '**' . DS . '**' . DS . '*.php')
         ) as $filename
) {
    require($filename);
}

// 404 handler
/** @var \Klein\Klein $router */
$router->respond(function( \Klein\Request $request ) {
    global $_SERVICE;
    $apiPaths = ['/api'];
    http_response_code(404);
    foreach ( $apiPaths as $path ) {
        if ( substr($request->pathname(),0,strlen($path)) == $path ) {
            die('{"error":404,"description":"Not found"}');
        }
    }
    return $_SERVICE['template']('404');
});

// Kickoff the request
$GLOBALS['router']->dispatch();
