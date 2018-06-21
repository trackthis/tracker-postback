<?php

/** @var \Klein\Klein $router */
$router->respond(function () {

    die('dinges?');

    // We need JSON & a broken $_POST for this middleware
    if ( $_SERVER['HTTP_CONTENT_TYPE'] !== 'application/json' ) return;
    if ( count($_POST) > 0 ) return;

    $body = file_get_contents('php://input');
    var_dump($body);

});
