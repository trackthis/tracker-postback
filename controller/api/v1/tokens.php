<?php
/** @var \Klein\Klein $router */

// Read all tokens
$router->respond('GET', '/api/v1/tokens', function () {
    global $_SERVICE;
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    header("Content-Type: application/json");

    if (!$isAdmin) {
        $_REQUEST['status'] = 403;
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm = $_SERVICE['odm'];
    die(json_encode(array_map(function ($token) {
        return array(
            'id'          => $token['id'],
            'description' => $token['description'],
        );
    }, $odm->table('token')->findAll())));
});
