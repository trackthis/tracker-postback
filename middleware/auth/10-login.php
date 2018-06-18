<?php

/** @var \Klein\Klein $router */
$router->respond(function () {
    global $_SERVICE;
    global $headers;

    // If the path starts with one of these, it returns a 403 in JSON instead of the login page
    $apiPaths = array(
        '/api',
    );

    // Api & not authenticated = 403
    if ((!$_REQUEST['auth'])) {

        // Handle API paths
        foreach ($apiPaths as $apiPath) {
            if (substr($_SERVER['REQUEST_URI'], 0, strlen($apiPath)) === $apiPath) {
                $_REQUEST['status'] = 403;
                header('Content-Type: application/json');
                die('{"error":403,"description":"Permission denied"}');
            }
        }

        header('Content-Type: text/html');
        die($_SERVICE['template']('login'));
    }
});
