<?php

/** @var \Klein\Klein $router */
$router->respond(function () {
    global $_SERVICE;
    $_REQUEST['auth'] = false;

    // Detect auth
    $raw = false;
    $raw = isset($_GET['token'])  ? $_GET['token']  : $raw;
    $raw = isset($_GET['auth'])   ? $_GET['auth']   : $raw;
    $raw = isset($_POST['token']) ? $_POST['token'] : $raw;
    $raw = isset($_POST['auth'])  ? $_POST['auth']  : $raw;
    $raw = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : $raw;
    if ($raw === false) {
        return;
    }

    // Strip possible prefixes
    $prefixes = array("Bearer ", "Token ");
    foreach ($prefixes as $prefix) {
        if (substr($raw, 0, strlen($prefix)) === $prefix) {
            $raw = substr($raw, strlen($prefix));
        }
    }

    /** @var \PicoDb\Database $odm */
    $odm = $_SERVICE['odm'];

    // Try to fetch the token
    $token = $odm->table('token')->eq('token',$raw)->findOne();
    if(is_null($token)) {
        return;
    }

    // Make sure it hasn't expired yet
    if( (intval($token['expires']) !== 0) && (intval($token['expires'])<time()) ) {
        return;
    }

    // We're authenticated, let's fetch the account
    $account = $odm->table('account')->eq('username',$token['username'])->findOne();
    if(is_null($account)) {
        return;
    }

    // Fetch it's mappings
    $mappings = $odm->table('mapping')->eq('token', $token['id'])->findAll();
    if(!count($mappings)) {
        return;
    }

    // Decode settings
    $account['settings']          = json_decode($account['settings'],true);
    $account['settings']['admin'] = isset($account['settings']['admin']) ? !!$account['settings']['admin'] : false;
    $account['settings']['token'] = $account['settings']['admin'] ? true : (isset($account['settings']['token']) ? !!$account['settings']['token'] : false);

    // Insert the auth into the request
    $_REQUEST['auth'] = array(
        'account' => $account,
        'method'  => 'api-token',
        'token'   => array(
            'mappings' => $mappings
        ),
    );


});