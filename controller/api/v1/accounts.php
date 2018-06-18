<?php

/** @var \Klein\Klein $router */
$router->respond('/api/v1/accounts',function () {
    global $_SERVICE;
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;

    if(!$isAdmin) {
        $_REQUEST['status'] = 403;
        die($_SERVICE['template']('denied'));
    }

    /** @var \PicoDb\Database $odm */
    $odm      = $_SERVICE['odm'];
    $accounts = $odm->table('account')->findAll();
    header("Content-Type: application/json");
    die(json_encode(array_map(function($account) {
        $account['settings'] = json_decode($account['settings'],true);
        return $account;
    }, $accounts)));
});
