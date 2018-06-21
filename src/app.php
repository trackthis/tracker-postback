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

// Catch pre-forked start
if (isset($_SERVER['argc'])) {
    require(APPROOT . DS . 'init.php');
}

// DEBUG function
// outputs yaml-like structure
if(!function_exists('prnt')) {
    function prnt( $data, $ret = false, $prefix = '' ) {
        $output = '';
        foreach ( $data as $key => $value ) {
            $output .= $prefix . $key . ':';
            switch(gettype($value)) {
                case 'boolean':
                    $output .= ' ' . ($value?'true':'false') . PHP_EOL;
                    break;
                case 'string':
                case 'integer':
                case 'number':
                case 'float':
                case 'double':
                    $output .= ' ' . $value . PHP_EOL;
                    break;
                case 'array':
                    $output .= PHP_EOL;
                    $output .= prnt( $value, true, $prefix.'  ');
                    break;
            }
        }
        if ($ret) {
            return $output;
        } else {
            echo $output;
            return null;
        }
    }
}

if(!function_exists('random_character')) {
    function random_character( $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
        return substr($alphabet,rand(0,strlen($alphabet)-1),1);
    }
}

if(!function_exists('random_string')) {
    function random_string( $length = 8, $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
        return implode(array_map('random_character',array_fill(0,$length,$alphabet)));
    }
}

if (!function_exists('breakpoint')) {
    function breakpoint( $key, $dumpval ) {
        // TODO: only in DEV mode
        $params = array_merge($_GET,$_POST);
        if( isset($params['break']) && $params['break'] == $key ) {
            header('Content-Type: text/plain');
            var_dump($dumpval);
            die();
        }
    }

//    // Example, pass break=INIT_REQUEST into the get params to dump $_REQUEST
//    breakpoint('INIT_REQUEST',$_REQUEST);
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
             glob(APPROOT . DS . 'controller' . DS . '**' . DS . '*.php'),
             glob(APPROOT . DS . 'controller' . DS . '**' . DS . '**' . DS . '*.php')
         ) as $filename
) {
    require_once($filename);
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
