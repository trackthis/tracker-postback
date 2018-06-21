<?php

/** @var \Klein\Klein $router */
$router->respond(function () {

    // Fetch content type
    $contentType = false;
    $contentType = isset($_SERVER['CONTENT_TYPE'])      ? $_SERVER['CONTENT_TYPE']      : $contentType;
    $contentType = isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : $contentType;

    // We need JSON & a broken $_POST for this middleware
    if ( $contentType !== 'application/json' ) return;
    if ( count($_POST) > 0 ) return;

    // Parse body
    try {
        $_POST = json_decode(file_get_contents('php://input'),true);
    } catch( Exception $e ) {
        $_POST = array();
    }
});
