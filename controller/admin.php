<?php
/** @var \Klein\Klein $router */

// Main admin interface
$router->respond('GET', '/admin',function () {
    global $_SERVICE;
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;

    // Only admins are allowed on this page
    if(!$isAdmin) {
        $_REQUEST['status'] = 403;
        die($_SERVICE['template']('denied'));
    }

    die($_SERVICE['template']('admin'));
});

// Account editing
$router->respond('GET', '/admin/[:username]', function( $request ) {
    global $_SERVICE;
    $isAdmin  = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    $username = $request->param('username', false);

    // Only admins are allowed on this page
    if(!$isAdmin) {
        $_REQUEST['status'] = 403;
        die($_SERVICE['template']('denied'));
    }

    // Username verification
    if( (!$username) || (!preg_match("/^[ a-zA-Z0-9\\-_]{3,}\$/", $username)) ) {
        $_REQUEST['status'] = 404;
        die($_SERVICE['template']('account-not-found', array(
            'username' => $username
        )));
    }

    // Fetch the account
    /** @var \PicoDb\Database $odm */
    $odm     = $_SERVICE['odm'];
    $account = $odm->table('account')->eq('username', $username)->findOne();
    if (is_null($account)) {
        $_REQUEST['status'] = 404;
        die($_SERVICE['template']('account-not-found', array(
            'username' => $username
        )));
    }

//    // Fetch the account's tokens
//    $tokens = $odm->table('token')->eq('username', $username)->findAll();

    // Reply with the edit page
    $account['settings'] = json_decode($account['settings'], true);
    die($_SERVICE['template']('admin-edit', array(
        "account" => $account,
//        "tokens"  => $tokens
    )));
});
