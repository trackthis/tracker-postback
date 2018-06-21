<?php

/** @var \Klein\Klein $router */
$router->respond('/',function () {
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    http_response_code(302);
    header('Location: /' . ($isAdmin?'admin':'tokens').'?token='.$_GET['token']);
    exit(0);
});
