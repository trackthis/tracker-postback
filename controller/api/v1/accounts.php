<?php
/** @var \Klein\Klein $router */

// Fetch all accounts
$router->respond('GET','/api/v1/accounts',function () {
    global $_SERVICE;
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    header("Content-Type: application/json");

    if(!$isAdmin) {
        $_REQUEST['status'] = 403;
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm      = $_SERVICE['odm'];
    $accounts = $odm->table('account')->findAllByColumn('username');
    die(json_encode(array_map(function($username) {
        return array( 'username' => $username );
    }, $accounts)));
});

// Fetch single account
$router->respond('GET', '/api/v1/accounts/[:username]', function( $request ) {
    global $_SERVICE;
    $isAdmin   = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    $fetchSelf = $_REQUEST['auth']['account']['username'] === $request->param('username', false);
    header("Content-Type: application/json");

    // Only admins may fetch anyone
    if (!($fetchSelf||$isAdmin)) {
        $_REQUEST['status'] = 403;
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm     = $_SERVICE['odm'];
    $account = $odm->table('account')->eq('username',$request->param('username'))->findOne();
    if(is_null($account)) {
        $_REQUEST['status'] = 404;
        die('{"error":404,"description":"The requested entity could not be found"}');
    }

    $account['settings'] = json_decode($account['settings'], true);
    die(json_encode($account));
});
