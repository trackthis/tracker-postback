<?php

$GLOBALS['router']->respond(function () {

    // Detect auth
    $auth = false;
    $auth = isset($_GET['token']) ? $_GET['token'] : false;
    $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : $auth;
    if ($auth === false) {
        return;
    }

    // Strip possible prefixes
    $prefixes = array("Bearer ", "Token ");
    foreach ($prefixes as $prefix) {
        if (substr($auth, 0, strlen($prefix)) === $prefix) {
            $auth = substr($auth, strlen($prefix));
        }
    }

    // Check
    var_dump('dinges');
});
