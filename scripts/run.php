#!/usr/bin/env php
<?php

// Catch non-cli usage
if (!isset($_SERVER['argc'])) {
    die('This is a CLI script');
}

// Load composer
require_once implode(DIRECTORY_SEPARATOR, array(
    __DIR__,
    '..',
    'vendor',
    'autoload.php',
));

//// Load services
//$_SERVICE = new Pimple\Container();
//foreach (array_merge(
//             glob(APPROOT . DS . 'service' . DS . '*.php'),
//             glob(APPROOT . DS . 'service' . DS . '**' . DS . '*.php')
//         ) as $filename
//) { require($filename); }

// Program entry
function main( $argc, $argv ) {

    // Verify params
    if ($argc < 2) {
        echo 'Usage:', PHP_EOL,
        '  ', $argv[0], ' <command> [OPTION=VALUE]', PHP_EOL,
        PHP_EOL,
        'Commands:', PHP_EOL;
        foreach (scandir(__DIR__, 1) as $entry) {
            if (substr($entry, 0, 1) == '.') continue;
            $path = __DIR__ . DS . $entry;
            if (!is_dir($path)) continue;
            echo '  ', $entry, PHP_EOL;
        }
        die();
    }

    // Parse given params
    $params = array();
    for ($i = 2; $i < $argc; $i++) {
        parse_str($argv[$i], $current);
        $params = array_merge($params, $current);
    }

    // Ensure the timeout is a number (default: 10)
    $params['timeout'] = isset($params['timeout']) ? intval($params['timeout']) : 10;
    $params['timeout'] = $params['timeout'] ? $params['timeout'] : 10;
    $startTime = isset($_SERVER['REQUEST_TIME']) ? intval($_SERVER['REQUEST_TIME']) : time();
    $stopTime = $startTime + $params['timeout'];

    /** @var \Finwo\Pipe\Target $runner */
    // Load tasks at hand
    $runner = require(__DIR__ . DS . $argv[1] . '/index.php');
    $runner->pipe(function($chunk) { if(is_null($chunk)) die('Done'.PHP_EOL); });

    // Start processing
    while(time()<$stopTime) {
        set_time_limit($params['timeout']*10);
        $runner->start->write(false);
    }
}

// Start the main program
main($_SERVER['argc'],$_SERVER['argv']);