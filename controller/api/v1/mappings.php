<?php
/** @var \Klein\Klein $router */

// Read all mappings for token
$router->respond('GET', '/api/v1/mappings', function ( \Klein\Request $request ) {
    global $_SERVICE;
    $settings          = $_REQUEST['auth']['account']['settings'];
    $settings['admin'] = isset($settings['admin']) ? $settings['admin'] : false;
    $settings['token'] = isset($settings['token']) ? $settings['token'] : false;
    $isAdmin           = isset($settings['admin']) ? $settings['admin'] : false;
    $params            = $request->params();
    header("Content-Type: application/json");

    // Start building the token query
    /** @var \PicoDb\Database $odm */
    $odm   = $_SERVICE['odm'];
    $query = $odm->table('token')->eq('id', intval($request->param('tokenid', 0)));

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

    // Fetch the token
    $token = $query->findOne();
    if(is_null($token)) {
        $_REQUEST['status'] = 404;
        die('{"error":404,"description":"Not found"}');
    }

    // Being here means we're allowed to fetch mappings
    die(json_encode($odm->table('mapping')->eq('token', $token['id'])->findAll()));
});

// Read single mapping
// TODO

// Write single mapping
$router->respond('POST', '/api/v1/mappings', function( \Klein\Request $request ) {
    global $_SERVICE;
    $settings          = $_REQUEST['auth']['account']['settings'];
    $settings['admin'] = isset($settings['admin']) ? $settings['admin'] : false;
    $settings['token'] = isset($settings['token']) ? $settings['token'] : false;
    $isAdmin           = isset($settings['admin']) ? $settings['admin'] : false;
    header("Content-Type: application/json");

    // Only admins may create mappings
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

    // Fetch the related token
    $query = $odm->table('token')->eq('username', $username);
    if(intval($request->param('token',false))) $query = $query->eq('id', $request->param('token'));
    $token = $query->findOne();
    if(is_null($token)) {
        $_REQUEST['status'] = 404;
        die('{"error":404,"description":"Not found"}');
    }

    // Build initial data
    $mapping = array( 'token' => $token['id'] );
    if (intval($request->param('id',false))) {
        $mapping = $odm->table('mapping')->eq('id',$request->param('id'))->findOne();
        if (is_null($mapping)) {
            $_REQUEST['status'] = 404;
            die('{"error":404,"description":"Not found"}');
        }
        if (intval($mapping['token'])!==intval($token['id'])) {
            $_REQUEST['status'] = 403;
            die('{"error":403,"description":"Permission denied"}');
        }
    }

    // Writable fields
    $mapping['remote']    = $request->param('remote'   , isset($mapping['remote'   ])?$mapping['remote'   ]:null);
    $mapping['tracker']   = $request->param('tracker'  , isset($mapping['tracker'  ])?$mapping['tracker'  ]:null);
    $mapping['translate'] = $request->param('translate', isset($mapping['translate'])?$mapping['translate']:null);

    // Save the record
    if (isset($mapping['id'])) {
        $result = $odm->table('mapping')->eq('id',$mapping['id'])->update($mapping);
    } else {
        $result = $odm->table('mapping')->insert($mapping);
    }
    $mapping['id'] = isset($mapping['id']) ? $mapping['id'] : $odm->getLastId();

//    if(!$result) {
//        /** @var PDO $con */
//        $con = $GLOBALS['pdoError'];
//        die($con->errorCode().':'.json_encode($con->errorInfo()));
//    }


//    // Fetch initial data
//    $mapping = array( 'account' => $username, 'token' => false );
//    if ($request->param('id',false)) {
//
//    }
//
//    // Write-once fields
//    if ( (!$mapping['token']) && $request->param('token',false) ) {
//        $mapping['token'] = $request->param('token');
//    }
//
//    // Writable fields




    die(json_encode(array($token,$mapping)));
});

// Delete single mapping
$router->respond('DELETE', '/api/v1/mappings/[i:id]', function ( \Klein\Request $request ) {
    global $_SERVICE;
    $settings          = $_REQUEST['auth']['account']['settings'];
    $settings['admin'] = isset($settings['admin']) ? $settings['admin'] : false;
    $settings['token'] = isset($settings['token']) ? $settings['token'] : false;
    $isAdmin           = isset($settings['admin']) ? $settings['admin'] : false;
    $params            = $request->params();
    header("Content-Type: application/json");

    // Only admins may delete mappings
    if (!$isAdmin) {
        $_REQUEST['status'] = 403;
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm   = $_SERVICE['odm'];

    // Fetch mapping
    $mapping = $odm->table('mapping')->eq('id', $request->param('id', 0))->findOne();
    if (is_null($mapping)) {
        $_REQUEST['status'] = 404;
        die('{"error":404,"description":"Not found"}');
    }

    // Prepare token query
    $query = $odm->table('token')->eq('id', $mapping['token']);

    // Add username filter if needed
    // For if someone later decides non-admins are allowed to do this
    if ( isset($params['account']) ) {
        if ( ($params['account']!==$_REQUEST['auth']['account']['username']) && (!$isAdmin) ) {
            $_REQUEST['status'] = 403;
            die('{"error":403,"description":"Permission denied"}');
        }
        $query = $query->eq('username', $params['account']);
    } elseif(!$isAdmin) {
        $query = $query->eq('username', $_REQUEST['auth']['account']['username']);
    }

    // Fetch the token
    $token = $query->findOne();
    if(is_null($token)) {
        $_REQUEST['status'] = 404;
        die('{"error":404,"description":"Not found"}');
    }

    // Being here means we're allowed to delete the mapping
    die(json_encode($odm->table('mapping')->eq('id', $mapping['id'])->remove()));
});
