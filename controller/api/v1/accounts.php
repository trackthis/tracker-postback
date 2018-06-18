<?php

/** @var \Klein\Klein $router */
$router->respond('/api/v1/accounts',function () {
    global $_SERVICE;
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;

    if(!$isAdmin) {
        $_REQUEST['status'] = 403;
        die($_SERVICE['template']('denied'));
    }

    echo 'Yo, ADMIN';
});
