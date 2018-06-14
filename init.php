<?php

// Entry point for SGI-like httpd
$f      = fopen('php://stdin', 'r');
$line   = str_replace("\r",'',str_replace("\r\n", "\n", fgets($f)));
$tokens = explode(' ', $line);
$_SERVER['REQUEST_METHOD'] = array_shift($tokens);
$_SERVER['REQUEST_URI']    = array_shift($tokens);

// Prevent loops
unset($_SERVER['argc']);

// Keep it simple
$statusCodes = array(
    200 => 'OK',
    400 => 'Bad Request',
    404 => 'Not Found',
    500 => 'Internal Server Error',
    501 => 'Not implemented',
);
$mimeTypes = array(
    'css'  => 'text/css',
    'htm'  => 'text/html',
    'html' => 'text/html',
    'js'   => 'text/javascript',
);
$status      = 200;
$headers     = array();
ob_start(function( $buffer ) {
    global $status;
    global $statusCodes;
    global $headers;
    $extra  = 'HTTP/1.0 '.$status.' '.$statusCodes[$status].PHP_EOL;
    $extra .= 'Content-Length: '.strlen($buffer) . PHP_EOL;
    foreach ($headers as $header) $extra .= $header . PHP_EOL;
    $extra .= PHP_EOL;
    return $extra . $buffer;
});

// Make sure we support this
if(!in_array($_SERVER['REQUEST_METHOD'],array('GET'))) {
    $status = 501;
    die('The requested method has not (yet) been implemented'.PHP_EOL);
}

// Some security
if (strpos($_SERVER['REQUEST_URI'], '..')!==false) {
    $status = 400;
    die('You\'ve send something we don\'t understand or allow'.PHP_EOL);
}

// Read headers
while(($line=str_replace("\r",'',str_replace("\r\n", "\n", fgets($f))))!="\n") {
    while(substr(rtrim($line), -1)=="\\") {
        $line  = rtrim($line);
        $line  = substr($line, 0, strlen($line)-1);
        $line .= str_replace("\r",'',str_replace("\r\n", "\n", fgets($f)));
    }
    list($key, $value) = array_map('trim', explode(':', $line, 2));
    $_SERVER['HTTP_'.strtoupper(str_replace('-','_',$key))] = $value;
}

// Read query string
$params = explode('?', $_SERVER['REQUEST_URI'], 2);
$path   = trim(rtrim(__DIR__,'/').'/web/'.trim(array_shift($params),'/'));
$ext    = @array_pop(explode('.',$path));
if(!function_exists('set_deep')) {
    function set_deep($path, &$dataHolder = array(), $value = null) {
        $keys = explode('.', $path);
        while (count($keys)) {
            $dataHolder = &$dataHolder[array_shift($keys)];
        }
        $dataHolder = $value;
    }
}
if(count($params)) {
    $params = array_shift($params);
    $_GET   = array();
    $variables = explode('&', $params);
    foreach ($variables as $variable) {
        $components = explode('=', $variable);
        $key        = str_replace(array( '[', ']' ), array( '.', '' ), urldecode(array_shift($components)));
        $value      = urldecode(array_shift($components));
        set_deep($key, $_GET, $value);
    }
}

// Directory index
if(is_dir($path)) {
    foreach(
        array(
            $path . '/index.php',
            $path . '/index.html',
            $path . '/index.htm',
        ) as $file
    ) {
        if(is_file($file)) {
            $path = $file;
            $ext  = @array_pop(explode('.',$path));
            break;
        }
    }
}

// Handle the actual file
if(is_file($path)) {
    switch($ext) {
        case 'php':
            include $path;
            exit(0);
        default:
            if(isset($mimeTypes[$ext])) $headers[]='Content-Type: '.$mimeTypes[$ext];
            $headers[]='Expires:'.date('c',time()+300);
            readfile($path);
            exit(0);
    }
}

$status = 404;
die('We could not find the page you\'re looking for.'.PHP_EOL);
