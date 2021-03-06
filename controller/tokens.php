<?php
/** @var \Klein\Klein $router */


$router->respond('/tokens',function ( \Klein\Request $request ) {
    global $_SERVICE;
    $isAdmin  = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    $params   = $request->params();

    // Fetch username to use
    // TODO: Make this code generic (it's used often)
    if ( isset($params['account']) ) {
        if ( ($params['account']!==$_REQUEST['auth']['account']['username']) && (!$isAdmin) ) {
            http_response_code(403);
            die('{"error":403,"description":"Permission denied"}');
        }
        $username = $params['account'];
    } else {
        $username = $_REQUEST['auth']['account']['username'];
    }

    header('Content-Type: text/html');
    die($_SERVICE['template']('token-overview', array(
        "username" => $username,
        "me"       => ($username===$_REQUEST['auth']['account']['username'])
    )));
});

$router->respond('GET', '/tokens/[i:id]', function( \Klein\Request $request ) {
    global $_SERVICE;
    $isAdmin  = isset($_REQUEST['auth']['account']['settings']['admin']) ? $_REQUEST['auth']['account']['settings']['admin'] : false;
    $params   = $request->params();

    // Verify 'account' param
    // Only admins may request any token
    if ( isset($params['account']) ) {
        if ( ($params['account']!==$_REQUEST['auth']['account']['username']) && (!$isAdmin) ) {
            http_response_code(403);
            die('{"error":403,"description":"Permission denied"}');
        }
        $username = $params['account'];
    } else {
        $username = $_REQUEST['auth']['account']['username'];
    }

    if ( (!$isAdmin) && ($username !== $_REQUEST['auth']['account']['username']) ) {
        http_response_code(302);
        header('Location: /tokens?token=' . $_GET['token']);
        exit(0);
    }

    // Prepare fallback url
    $fburl    = '/tokens';
    $params   = explode('?', $_SERVER['REQUEST_URI'], 2);array_shift($params);
    if(count($params)) $fburl .= '?' . array_shift($params );

    // Fetch the token
    /** @var \PicoDb\Database $odm */
    $odm   = $_SERVICE['odm'];
    $token = $odm->table('token')->eq('id', $request->param('id', 0 ))->findOne();
    if (is_null($token)) {
        http_response_code(302);
        header('Location: ' . $fburl);
        exit(0);
    }

    // Make sure the (given) account owns the token
    if ( (!$isAdmin) && ($token['username'] !== $username) ) {
        http_response_code(302);
        header('Location: ' . $fburl);
        exit(0);
    }

    // Remove token itself if not allowed to see it
    if ( !$_REQUEST['auth']['account']['settings']['token'] ) {
        unset($token['token']);
    }

    // Return the page
    header('Content-Type: text/html');
    die($_SERVICE['template']('token-edit', array(
        'title' => 'Edit token: ' . (isset($token['token'])?$token['token']:'[[token.description]]'),
        'edit'  => true,
        'token' => $token,
    )));
});

$router->respond('GET', '/tokens/new', function() {
    global $_SERVICE;

    header('Content-Type: text/html');
    die($_SERVICE['template']('token-edit', array(
        'title' => 'New token',
        'new'   => true,
    )));
});
