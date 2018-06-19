<?php
/** @var \Klein\Klein $router */

// Read all tokens
$router->respond('GET', '/api/v1/tokens', function () {
    global $_SERVICE;
    $settings          = $_REQUEST['auth']['account']['settings'];
    $settings['admin'] = isset($settings['admin']) ? $settings['admin'] : false;
    $settings['token'] = isset($settings['token']) ? $settings['token'] : false;
    $isAdmin  = isset($settings['admin']) ? $settings['admin'] : false;
    header("Content-Type: application/json");

    // Start building the query
    /** @var \PicoDb\Database $odm */
    $odm   = $_SERVICE['odm'];
    $query = $odm->table('token');

    // Add username filter if needed
    if ( isset($_GET['account']) ) {
        if ( ($_GET['account']!==$_REQUEST['auth']['account']['username']) && (!$isAdmin) ) {
            $_REQUEST['status'] = 403;
            die('{"error":403,"description":"Permission denied"}');
        }
        $query = $query->eq('username', $_GET['account']);
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
