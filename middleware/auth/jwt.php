<?php

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Parser;

$GLOBALS['router']->respond(function () {
    global $_SERVICE;

    // Detect auth
    $raw = false;
    $raw = isset($_GET['token']) ? $_GET['token'] : $raw;
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

    // Parse token
    $token = (new Parser())->parse($raw);

    // Verify header
    if ($token->getHeader('typ') !== 'JWT') {
        return;
    }
    if ($token->getHeader('alg') !== 'ES256') {
        return;
    }

    // Fetch it's user
    $username = $token->getClaim('usr', false);
    if (gettype($username) !== 'string') {
        return;
    }

    // Try to fetch the user from DB
    /** @var \PicoDb\Database $odm */
    $odm     = $_SERVICE['odm'];
    $account = $odm->table('account')->eq('username', $username)->findOne();


    var_dump($raw);
    var_dump($account);
    var_dump($token->getHeaders());
});
