<?php
/** @var \Klein\Klein $router */

// Read all accounts
$router->respond('GET', '/api/v1/accounts', function () {
    global $_SERVICE;
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    header("Content-Type: application/json");

    if (!$isAdmin) {
        http_response_code(403);
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
        http_response_code(403);
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm     = $_SERVICE['odm'];
    $account = $odm->table('account')->eq('username', $request->param('username'))->findOne();
    if (is_null($account)) {
        http_response_code(404);
        die('{"error":404,"description":"The requested entity could not be found"}');
    }

    $account['settings'] = json_decode($account['settings'], true);
    die(json_encode($account));
});

// Write an account
$router->respond('POST', '/api/v1/accounts', function( \Klein\Request $request ) {
    global $_SERVICE;
    $isAdmin = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    $params  = $request->params();
    header("Content-Type: application/json");

    // Only admins are allowed to create/update users
    if (!$isAdmin) {
        http_response_code(403);
        die('{"error":403,"description":"Permission denied"}');
    }

    // Validate given username
    if(!preg_match("/^[ a-zA-Z0-9\\-_\\.]{3,}\$/", $params['username'])) {
        http_response_code(422);
        die(json_encode(array(
            "error"       => 422,
            "description" => "The username did not meet the requirements: /^[ a-zA-Z0-9\\-_\\.]{3,}\$/"
        )));
    }
    /** @var \PicoDb\Database $odm */
    $odm    = $_SERVICE['odm'];

    // Check if it already exists
    if($account = $odm->table('account')->eq('username',$params['username'])->findOne()) {
        // Update an existing user
        $account['settings']          = json_decode($account['settings'],true);
        $account['pubkey']            = $request->param('pubkey', $account['pubkey']);
        $account['settings']['admin'] = $request->param('isAdmin', $account['settings']['admin']);
        $account['settings']['token'] = $request->param('showToken', $account['settings']['token']);
        $account['settings']          = json_encode($account['settings']);
        die(json_encode($odm->table('account')->eq('username',$account['username'])->update($account)));
    } else {
        // Create new account record
        die(json_encode($odm->table('account')->insert(array(
            'username' => $params['username'],
            'pubkey'   => $params['pubkey'],
            'settings' => json_encode(array(
                'admin' => filter_var($request->param('isAdmin',false), FILTER_VALIDATE_BOOLEAN),
                'token' => filter_var($request->param('isAdmin',$request->param('showToken',false)), FILTER_VALIDATE_BOOLEAN)
            )),
        ))));
    }
});

// Delete an account
$router->respond('DELETE', '/api/v1/accounts/[:username]', function ($request) {
    global $_SERVICE;
    $isAdmin   = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    $fetchSelf = $_REQUEST['auth']['account']['username'] === $request->param('username', false);
    header("Content-Type: application/json");

    // Only admins may delete anyone
    if (!($fetchSelf || $isAdmin)) {
        http_response_code(403);
        die('{"error":403,"description":"Permission denied"}');
    }

    // Fetch the account first
    /** @var \PicoDb\Database $odm */
    $odm     = $_SERVICE['odm'];
    $account = $odm->table('account')->eq('username', $request->param('username'))->findOne();
    if (is_null($account)) {
        http_response_code(404);
        die('{"error":404,"description":"The requested entity could not be found"}');
    }

    // Fetch all API tokens
    $tokens = $odm->table('token')->eq('username', $account['username'])->findAllByColumn('id');
    $result = true;

    // Attempt deletion only when we have tokens
    if(count($tokens)) {

        // Delete all mappings
        $result &= $odm->table('mapping')->in('token',$tokens)->remove();

        // Delete all API tokens
        $result &= $odm->table('token')->in('id', $tokens)->remove();
    }

    // Delete the account itself
    $result &= $odm->table('account')->eq('username', $account['username'])->remove();

    // Return the deleted account
    $account['settings'] = json_decode($account['settings'], true);
    die(json_encode($account));
});
