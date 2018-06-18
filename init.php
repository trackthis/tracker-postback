<?php

// Entry point for SGI-like httpd
$f      = fopen('php://stdin', 'r');
$line   = str_replace("\r",'',str_replace("\r\n", "\n", fgets($f)));
$tokens = explode(' ', $line);
$docroot = __DIR__.DS.'web';
$_SERVER['REQUEST_METHOD'] = array_shift($tokens);
$_SERVER['REQUEST_URI']    = array_shift($tokens);

// Prevent loops
unset($_SERVER['argv']);
unset($_SERVER['argc']);

// Keep it simple
$statusCodes = array(
    200 => 'OK',
    302 => 'Found',
    400 => 'Bad Request',
    403 => 'Permission denied',
    404 => 'Not Found',
    422 => 'Unprocessable Entity',
    500 => 'Internal Server Error',
    501 => 'Not implemented',
);
$mimeTypes = array(
    'css'  => 'text/css',
    'htm'  => 'text/html',
    'html' => 'text/html',
    'js'   => 'text/javascript',
);
$_REQUEST['status']  = 200;
$_REQUEST['headers'] = array();
ob_start(function( $buffer ) {
    global $statusCodes;
    $extra  = 'HTTP/1.0 '.$_REQUEST['status'].' '.$statusCodes[$_REQUEST['status']].PHP_EOL;
    $extra .= 'Content-Length: '.strlen($buffer) . PHP_EOL;
    foreach ($_REQUEST['headers'] as $header) $extra .= $header . PHP_EOL;
    $nativeHeaders = php_sapi_name() === 'cli' ? xdebug_get_headers() : headers_list();
    foreach ($nativeHeaders as $header) $extra .= $header . PHP_EOL;
    $extra .= PHP_EOL;
    return $extra . $buffer;
});

// Make sure we support this
if(!in_array($_SERVER['REQUEST_METHOD'],array('GET','POST','DELETE'))) {
    $_REQUEST['status'] = 501;
    header('Content-Type: application/json');
    die('{"error":501,"description":"The requested method has not (yet) been implemented"}');
}

// Some security
if (strpos($_SERVER['REQUEST_URI'], '..')!==false) {
    $status = 400;
    die('You\'ve send something we don\'t understand or allow'.PHP_EOL);
}

// Helper functions
if(!function_exists('set_deep')) {
    function set_deep($path, &$dataHolder = array(), $value = null) {
        $keys = explode('.', $path);
        while (count($keys)) {
            $dataHolder = &$dataHolder[array_shift($keys)];
        }
        $dataHolder = $value;
    }
}
if(!function_exists('decodeQuery')) {
    function decodeQuery($str) {
        $out       = array();
        $variables = explode('&', $str);
        foreach ($variables as $variable) {
            $components = explode('=', $variable);
            $key        = str_replace(array('[', ']'), array('.', ''), urldecode(array_shift($components)));
            $value      = urldecode(array_shift($components));
            set_deep($key, $out, $value);
        }
        return $out;
    }
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

if (isset($_SERVER['HTTP_CONTENT_LENGTH'])) {
    $_REQUEST['body'] = fread($f,intval($_SERVER['HTTP_CONTENT_LENGTH']));

    if (isset($_SERVER['HTTP_CONTENT_TYPE'])) {
        switch($_SERVER['HTTP_CONTENT_TYPE']) {
            case 'application/json': $_POST = json_decode($_REQUEST['body'], true); break;
            default:
                $_REQUEST['status'] = 400;
                die('{"error":400,"description":"Invalid request - unsupported body content type"}');
        }
    }
}

// Parse query string
$params = explode('?', $_SERVER['REQUEST_URI'], 2);
$path   = trim($docroot.DS.trim(array_shift($params),'/'));
$ext    = @array_pop(explode('.',$path));
if(count($params)) {
    $params = array_shift($params);
    $_GET   = decodeQuery($params);
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

// Check for an app
if(is_file(__DIR__ . DS . 'src' . DS . 'app.php')) {
    include __DIR__ . DS . 'src' . DS . 'app.php';
    exit(0);
}

// Too bad
$status = 404;
die('We could not find the page you\'re looking for.'.PHP_EOL);
