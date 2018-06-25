<?php

namespace Tracker\Adapter;

abstract class Adapter {

    /**
     * Initialize a new adapter
     *
     * Detects which adapter to use by the scheme inside the URI
     *
     * @param string $uri
     *
     * @return AbstractAdapter|null
     */
    public static function create( $uri ) {

        // Build the class name
        $scheme    = ucfirst(strtolower(parse_url($uri, PHP_URL_SCHEME)));
        $className = "\\Tracker\\Adapter\\" . $scheme . 'Adapter';

        // Check if it actually exists
        if (!class_exists($className)) {
            return null;
        }

        // Create & return
        return new $className($uri);
    }
}