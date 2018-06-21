<?php

/** @var \Klein\Klein $router */
$router->respond('/account',function () {
    global $_SERVICE;
    header('Content-Type: text/html');
    die($_SERVICE['template']('account', array(
        'account' => $_REQUEST['auth']['account']
    )));
});
