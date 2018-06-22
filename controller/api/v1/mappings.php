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
            http_response_code(403);
            die('{"error":403,"description":"Permission denied"}');
        }
        $query = $query->eq('username', $params['account']);
    } elseif(!$isAdmin) {
        $query = $query->eq('username', $_REQUEST['auth']['account']['username']);
    }

    // Fetch the token
    $token = $query->findOne();
    if(is_null($token)) {
        http_response_code(404);
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
    $settings['token'] = $settings['admin'] ? true : (isset($settings['token']) ? $settings['token'] : false);
    $isAdmin           = $settings['admin'];
    $username          = false;
    header("Content-Type: application/json");

    // Only admins may create/update mappings
    if (!$isAdmin) {
        http_response_code(403);
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm = $_SERVICE['odm'];

    // Fetch username to use
    // TODO: Make this code generic (it's used often)
    $params = $request->params();
    if ( isset($params['account']) ) {
        if ( ($params['account']!==$_REQUEST['auth']['account']['username']) && (!$isAdmin) ) {
            http_response_code(403);
            die('{"error":403,"description":"Permission denied"}');
        }
        $username = $params['account'];
    } elseif (!$isAdmin) {
        $username = $_REQUEST['auth']['account']['username'];
    }

    // Fetch the token (something about ownership)
    $query = $odm->table('token');
    if($username) $query = $query->eq('username', $username);
    if(intval($request->param('token',false))) $query = $query->eq('id', intval($request->param('token')));
    $token = $query->findOne();

    if(is_null($token)) {
        http_response_code(404);
        die('{"error":404,"description":"Not found"}');
    }

    // Build initial data
    $mapping = array( 'token' => $token['id'] );
    if (intval($request->param('id',false))) {
        $mapping = $odm->table('mapping')->eq('id',$request->param('id'))->findOne();
        if (is_null($mapping)) {
            http_response_code(404);
            die('{"error":404,"description":"Not found"}');
        }
        if (intval($mapping['token'])!==intval($token['id'])) {
            http_response_code(403);
            die('{"error":403,"description":"Permission denied"}');
        }
    }

    // Writable fields
    $mapping['source']    = $request->param('source'   , isset($mapping['source'   ])?$mapping['source'   ]:null);
    $mapping['field']     = $request->param('field'    , isset($mapping['field'    ])?$mapping['field'    ]:null);
    $mapping['translate'] = $request->param('translate', isset($mapping['translate'])?$mapping['translate']:null);

    // Save the record
    if (isset($mapping['id'])) {
        $result = $odm->table('mapping')->eq('id',$mapping['id'])->update($mapping);
    } else {
        $result = $odm->table('mapping')->insert($mapping);
    }

    // Sorry, PicoDB has no way to extract errors
    if(!$result) {
        http_response_code(400);
        die('{"error":400,"description":"Bad request"}');
    }

    // Add the ID to the output if needed
    $mapping['id'] = isset($mapping['id']) ? $mapping['id'] : $odm->getLastId();

    // Return the mapping record
    die(json_encode($mapping));
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
        http_response_code(403);
        die('{"error":403,"description":"Permission denied"}');
    }

    /** @var \PicoDb\Database $odm */
    $odm   = $_SERVICE['odm'];

    // Fetch mapping
    $mapping = $odm->table('mapping')->eq('id', $request->param('id', 0))->findOne();
    if (is_null($mapping)) {
        http_response_code(404);
        die('{"error":404,"description":"Not found"}');
    }

    // Prepare token query
    $query = $odm->table('token')->eq('id', $mapping['token']);

    // Add username filter if needed
    // For if someone later decides non-admins are allowed to do this
    if ( isset($params['account']) ) {
        if ( ($params['account']!==$_REQUEST['auth']['account']['username']) && (!$isAdmin) ) {
            http_response_code(403);
            die('{"error":403,"description":"Permission denied"}');
        }
        $query = $query->eq('username', $params['account']);
    } elseif(!$isAdmin) {
        $query = $query->eq('username', $_REQUEST['auth']['account']['username']);
    }

    // Fetch the token
    $token = $query->findOne();
    if(is_null($token)) {
        http_response_code(404);
        die('{"error":404,"description":"Not found"}');
    }

    // Being here means we're allowed to delete the mapping
    die(json_encode($odm->table('mapping')->eq('id', $mapping['id'])->remove()));
});
