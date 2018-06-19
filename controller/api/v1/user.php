<?php
/** @var \Klein\Klein $router */

// Fetch self
$router->respond('GET', '/api/v1/user/me', function() {
    header('Content-Type: application/json');
    die(json_encode($_REQUEST['auth']['account']));
});
