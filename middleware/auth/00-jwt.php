<?php

/** @var \Klein\Klein $router */
$router->respond(function () {
    global $_SERVICE;
    breakpoint('jwt-request', $_REQUEST);
    $_REQUEST['auth'] = false;

    function url2b64($data) {
        if ($remainder = strlen($data) % 4) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return strtr($data,'-_','+/');
    }
    function b64urldecode($data) {
        return base64_decode(url2b64($data));
    }

    // Detect auth
    $raw = false;
    $raw = isset($_GET['token'])  ? $_GET['token']  : $raw;
    $raw = isset($_GET['auth'])   ? $_GET['auth']   : $raw;
    $raw = isset($_POST['token']) ? $_POST['token'] : $raw;
    $raw = isset($_POST['auth'])  ? $_POST['auth']  : $raw;
    $raw = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : $raw;
    breakpoint('jwt-raw', $raw);
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
    breakpoint('jwt-trimmed', $raw);

    // Split into parts
    $parts = explode('.',$raw);
    if(count($parts) !== 3) return;
    $header    = array_shift($parts);
    $payload   = array_shift($parts);
    $data      = $header.'.'.$payload;
    $header    = json_decode(b64urldecode($header),true);
    $payload   = json_decode(b64urldecode($payload),true);
    $signature = bin2hex(b64urldecode(array_shift($parts)));

    breakpoint('jwt-header', $header);
    breakpoint('jwt-payload', $payload);
    breakpoint('jwt-signature', $signature);

    // Verify header
    if ((isset($header['typ'])?$header['typ']:false) !== 'JWT') return;
    if ((isset($header['alg'])?$header['alg']:false) !== 'ES256') return;
    // TODO: verify expiry (after dev is done)

    // Fetch it's user
    $username = isset($payload['usr']) ? $payload['usr'] : false;
    if(gettype($username) !== 'string') return;


    breakpoint('jwt-username', $username);

    // Fetch the user from DB
    /** @var \PicoDb\Database $odm */
    $odm     = $_SERVICE['odm'];
    $account = $odm->table('account')->eq('username', $username)->findOne();
    breakpoint('jwt-account', $account);
    if (is_null($account)) return;

    // Build hash
    $hash = hash('sha256', $data);
    breakpoint('jwt-data', $data);
    breakpoint('jwt-hash', $hash);

    // Verify the signature
    $pubkey = $account['pubkey'];
    breakpoint('jwt-pubkey', $pubkey);
    $result = array();
    exec('node '.APPROOT."/src/sigcheck.js  ${hash} ${pubkey} ${signature}",$result);
    $valid = json_decode(array_shift($result));
    if (!$valid) {
        return;
    }

    // Decode settings
    $account['settings']          = json_decode($account['settings'],true);
    $account['settings']['admin'] = isset($account['settings']['admin']) ? !!$account['settings']['admin'] : false;
    $account['settings']['token'] = $account['settings']['admin'] ? true : (isset($account['settings']['token']) ? !!$account['settings']['token'] : false);

    // Insert the auth into the request
    $_REQUEST['auth'] = array(
        'account' => $account,
        'method'  => 'JWT',
        'token'   => $raw,
    );
});
