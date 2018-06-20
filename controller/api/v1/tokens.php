<?php
/** @var \Klein\Klein $router */

// Read all tokens
$router->respond('GET', '/api/v1/tokens', function ( \Klein\Request $request ) {
    global $_SERVICE;
    $settings          = $_REQUEST['auth']['account']['settings'];
    $settings['admin'] = isset($settings['admin']) ? $settings['admin'] : false;
    $settings['token'] = isset($settings['token']) ? $settings['token'] : false;
    $isAdmin           = isset($settings['admin']) ? $settings['admin'] : false;
    $params            = $request->params();
    header("Content-Type: application/json");

    // Start building the query
    /** @var \PicoDb\Database $odm */
    $odm   = $_SERVICE['odm'];
    $query = $odm->table('token');

    // Add username filter if needed
    if ( isset($params['account']) ) {
        if ( ($params['account']!==$_REQUEST['auth']['account']['username']) && (!$isAdmin) ) {
            $_REQUEST['status'] = 403;
            die('{"error":403,"description":"Permission denied"}');
        }
        $query = $query->eq('username', $params['account']);
    } elseif(!$isAdmin) {
        $query = $query->eq('username', $_REQUEST['auth']['account']['username']);
    }

    die(json_encode(array_map(function ($token) use ($settings) {
        $result = array(
            'id'          => intval($token['id']),
            'description' => $token['description'],
            'expires'     => intval($token['expires']),
        );
        if ( $settings['token'] ) {
            $result['token'] = $token['token'];
        }
        return $result;
    }, $query->findAll())));
});

// Read single token
// TODO

// Write token
$router->respond('POST', '/api/v1/tokens', function( \Klein\Request $request ) {
    global $_SERVICE;
    $settings          = $_REQUEST['auth']['account']['settings'];
    $settings['admin'] = isset($settings['admin']) ? $settings['admin'] : false;
    $settings['token'] = isset($settings['token']) ? $settings['token'] : false;
    $isAdmin           = isset($settings['admin']) ? $settings['admin'] : false;
    $params            = $request->params();
    header("Content-Type: application/json");

    // Only admins may create/update tokens
    if (!$isAdmin) {
        $_REQUEST['status'] = 403;
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm = $_SERVICE['odm'];

    // Fetch username to use
    // TODO: Make this code generic (it's used often)
    $params = $request->params();
    if ( isset($params['account']) ) {
        if ( ($params['account']!==$_REQUEST['auth']['account']['username']) && (!$isAdmin) ) {
            $_REQUEST['status'] = 403;
            die('{"error":403,"description":"Permission denied"}');
        }
        $username = $params['account'];
    } elseif(!$isAdmin) {
        $username = $_REQUEST['auth']['account']['username'];
    }

    // Build initial data
    $token = array( 'username' => $username );
    if (intval($request->param('id',false))) {
        $token = $odm->table('token')->eq('id',$request->param('id'))->findOne();
        if (is_null($token)) {
            $_REQUEST['status'] = 404;
            die('{"error":404,"description":"Not found"}');
        }
        if ($username !== $token['username']) {
            $_REQUEST['status'] = 403;
            die('{"error":403,"description":"Permission denied"}');
        }
    }

    // Writable fields
    $token['description'] = $request->param('description', isset($token['description'])?$token['description']:null);
    $token['expires'    ] = $request->param('expires'    , isset($token['expires'    ])?$token['expires'    ]:0);

    // Save the record
    if (isset($token['id'])) {
        $result = $odm->table('token')->eq('id',$token['id'])->update($token);
    } else {
        $token['token'] = random_string(48);
        $result         = $odm->table('token')->insert($token);
    }

    // Sorry, PicoDB has no way to extract errors
    if(!$result) {
        $_REQUEST['status'] = 400;
        die('{"error":400,"description":"Bad request"}');
    }

    // Add the ID to the output if needed
    if (!isset($token['id'])) {
        $token['id']    = $odm->getLastId();
        $token['token'] = $odm->table('token')->eq('id',$token['id'])->findOneColumn('token');
    }

    // Return the token record
    die(json_encode($token));
});

// Delete token
// TODO
