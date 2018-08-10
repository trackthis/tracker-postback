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

    // Another helper function
    function guid() {
        if(function_exists('com_create_guid')) {
            return trim(com_create_guid(),'{}');
        } else if (function_exists('uuid_create')) {
            return uuid_create();
        } else if (function_exists('openssl_random_pseudo_bytes')) {
            $data = openssl_random_pseudo_bytes(16);
            $data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // set version to 0100
            $data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // set bits 6-7 to 10
            return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
        } else {
          mt_srand((double)microtime() * 10000);
          $charid = strtolower(md5(uniqid(rand(), true)));
          $hyphen = chr(45); // "-"
          return substr($charid,  0,  8).$hyphen.
                 substr($charid,  8,  4).$hyphen.
                 substr($charid, 12,  4).$hyphen.
                 substr($charid, 16,  4).$hyphen.
                 substr($charid, 20, 12);
        }
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
    $data = array_merge(array('guid'=>guid()),$request->params());
    $message = $adapter->record($data);
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
