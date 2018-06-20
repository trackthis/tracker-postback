<?php
/** @var \Klein\Klein $router */

// Read all accounts
$router->respond('GET', '/api/v1/accounts', function () {
    global $_SERVICE;
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    header("Content-Type: application/json");

    if (!$isAdmin) {
        $_REQUEST['status'] = 403;
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm      = $_SERVICE['odm'];
    $accounts = $odm->table('account')->findAllByColumn('username');
    die(json_encode(array_map(function ($username) {
        return array('username' => $username);
    }, $accounts)));
});

// Read single account
$router->respond('GET', '/api/v1/accounts/[:username]', function ($request) {
    global $_SERVICE;
    $isAdmin   = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    $fetchSelf = $_REQUEST['auth']['account']['username'] === $request->param('username', false);
    header("Content-Type: application/json");

    // Only admins may fetch anyone
    if (!($fetchSelf || $isAdmin)) {
        $_REQUEST['status'] = 403;
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm     = $_SERVICE['odm'];
    $account = $odm->table('account')->eq('username', $request->param('username'))->findOne();
    if (is_null($account)) {
        $_REQUEST['status'] = 404;
        die('{"error":404,"description":"The requested entity could not be found"}');
    }

    $account['settings'] = json_decode($account['settings'], true);
    die(json_encode($account));
});

// Write an account
$router->respond('POST', '/api/v1/accounts', function() {
    global $_SERVICE;
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    header("Content-Type: application/json");

    // Only admins are allowed to create users
    if (!$isAdmin) {
        $_REQUEST['status'] = 403;
        die('{"error":403,"description":"Permission denied"}');
    }

    // Validate given username
    if(!preg_match("/^[ a-zA-Z0-9\\-_]{3,}\$/", $_POST['username'])) {
        $_REQUEST['status'] = 422;
        die(json_encode(array(
            "error"       => 422,
            "description" => "The username did not meet the requirements: /^[ a-zA-Z0-9\\-_]{3,}\$/"
        )));
    }

    // Create new account record
    $account = array(
        'username' => $_POST['username'],
        'pubkey'   => $_POST['pubkey'],
        'settings' => json_encode(array(
            'admin' => isset($_POST['isAdmin']) ? filter_var($_POST['isAdmin'], FILTER_VALIDATE_BOOLEAN) : false,
        )),
    );

    // Insert into database
    /** @var \PicoDb\Database $odm */
    $odm    = $_SERVICE['odm'];
    $result = $odm->table('account')->insert($account);

    // Return what happened
    die('{"success":'.($result?'true':'false').'}');
});

// Delete an account
$router->respond('DELETE', '/api/v1/accounts/[:username]', function ($request) {
    global $_SERVICE;
    $isAdmin   = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    $fetchSelf = $_REQUEST['auth']['account']['username'] === $request->param('username', false);
    header("Content-Type: application/json");

    // Only admins may delete anyone
    if (!($fetchSelf || $isAdmin)) {
        $_REQUEST['status'] = 403;
        die('{"error":403,"description":"Permission denied"}');
    }

    // Fetch the account first
    /** @var \PicoDb\Database $odm */
    $odm     = $_SERVICE['odm'];
    $account = $odm->table('account')->eq('username', $request->param('username'))->findOne();
    if (is_null($account)) {
        $_REQUEST['status'] = 404;
        die('{"error":404,"description":"The requested entity could not be found"}');
    }

    // Fetch all API tokens
    $tokens = $odm->table('token')->eq('username', $account['username'])->findAllByColumn('id');

    // Delete all mappings
    $result = $odm->table('mapping')->in('token',$tokens)->remove();

    // Delete all API tokens
    $result &= $odm->table('token')->in('id', $tokens)->remove();

    // Delete the account itself
    $result &= $odm->table('account')->eq('username', $account['username'])->remove();

    // Return the deleted account
    $account['settings'] = json_decode($account['settings'], true);
    die(json_encode($account));
});
