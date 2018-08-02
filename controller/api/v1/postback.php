<?php
/** @var \Klein\Klein $router */

// Handle a postback
$router->respond('GET', '/api/v1/postback', function ( \Klein\Request $request ) {
    global $_SERVICE;
    header("Content-Type: application/json");

    // Ensure API tokens are used
    if($_REQUEST['auth']['method'] !== 'api-token') {
        http_response_code(403);
        die('{"error":403","description":"Permission denied"}');
    }

    // Helper function
    function db2uri( $settings ) {
        if(is_string($settings)) return $settings;
        return build_url(array(
            'scheme' => isset($settings['driver'])   ? $settings['driver']         : null,
            'user'   => isset($settings['username']) ? $settings['username']       : null,
            'pass'   => isset($settings['password']) ? $settings['password']       : null,
            'host'   => isset($settings['hostname']) ? $settings['hostname']       : null,
            'port'   => isset($settings['port'])     ? $settings['port']           : null,
            'path'   => isset($settings['database']) ? ('/'.$settings['database']) : null,
        ));
    }

    // Fetch the target
    $token      = $_REQUEST['auth']['token'];
    $defaultUri = db2uri(\Finwo\Framework\Config\Config::get('tracker.default'));
    $target     = empty($token['target']) ? $defaultUri : $token['target'];
    if ( $target !== $defaultUri ) {
        $adapter = new \Tracker\Adapter\BufferAdapter($target); // Add buffer to non-default
    } else {
        $adapter    = \Tracker\Adapter\Adapter::create($target);
    }
    if(is_null($adapter)) {
        http_response_code(422);
        die('{"error":422,"description":"Unprocessable Entity"}');
    }

    // Pass the mappings towards the adapter
    $adapter->setFields($_REQUEST['auth']['token']['mappings']);

    // Let's write what we got
    $message = $adapter->record($request->params());
    if($message) {
        $code = $message == 'idx_unique' ? 400 : 500;
        http_response_code($code);
        die(json_encode(array(
            "success"     => false,
            "error"       => $code,
            "description" => "Internal Server Error",
            "message"     => $message
        )));
    } else {
        die(json_encode(array(
            "success" => true,
        )));
    }
});
