<?php

// Definitions
if (!defined('DS')) {
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('APPROOT')) {
    define('APPROOT', rtrim(dirname(__DIR__), DS));
}

// Don't spam the logs
error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );

// Don't show the errors to users
ini_set("display_errors", 0);

// String formatter
if(!function_exists('string_format')) {
    /**
     * String formatter
     *
     * @param string $template
     * @param array $data
     *
     * @return string
     */
    function string_format( $template, $data ) {
        switch(gettype($data)) {
            case 'boolean':
            case 'integer':
            case 'double':
            case 'float':
            case 'string':
                $args = func_get_args();
                array_shift($args);
                return string_format($template, $args);
            case 'object':
            case 'array':
                $data = array_flatten($data);
                break;
            default:
                return $template;
        }
        foreach ($data as $key => $value) {
            $template = str_replace(sprintf("{%s}", $key), $value, $template);
        }
        return $template;
    }
}

// DEBUG function
// outputs yaml-like structure
if(!function_exists('prnt')) {
    /**
     * Returns a variable in a yaml-like format
     *
     * @param mixed $data
     * @param bool $ret
     * @param string $prefix
     * @return null|string
     */
    function prnt( $data, $ret = false, $prefix = '' ) {
        $output = '';
        foreach ( $data as $key => $value ) {
            $output .= $prefix . $key . ':';
            switch(gettype($value)) {
                case 'boolean':
                    $output .= ' ' . ($value?'true':'false') . PHP_EOL;
                    break;
                case 'NULL':
                    $output .= ' NULL' . PHP_EOL;
                    break;
                case 'string':
                case 'integer':
                case 'number':
                case 'float':
                case 'double':
                    $output .= ' ' . $value . PHP_EOL;
                    break;
                case 'array':
                    $output .= PHP_EOL;
                    $output .= prnt( $value, true, $prefix.'  ');
                    break;
            }
        }
        if ($ret) {
            return $output;
        } else {
            echo $output;
            return null;
        }
    }
}

if(!function_exists('build_url')) {
    /**
     * Reverse of parse_url
     *
     * @param array $parts
     * @return string
     */
    function build_url(array $parts) {
        return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') .
            (isset($parts['user']) ? "{$parts['user']}" : '') .
            (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
            (isset($parts['user']) ? '@' : '') .
            (isset($parts['host']) ? "{$parts['host']}" : '') .
            (isset($parts['port']) ? ":{$parts['port']}" : '') .
            (isset($parts['path']) ? "{$parts['path']}" : '') .
            (isset($parts['query']) ? "?{$parts['query']}" : '') .
            (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }
}

if(!function_exists('array_flatten')) {
    /**
     * Flattens a deep array into a flat dot-separated array
     *
     * @param array $data
     * @param string $parentKey
     * @return array
     */
    function array_flatten( $data, $parentKey = '' ) {
        $output = array();
        foreach ($data as $key => $value) {
            $compositeKey = strlen($parentKey) ? $parentKey.'.'.$key : $key;
            switch(gettype($value)) {
                case 'object':
                    if (method_exists($value,'__toArray')) {
                        $value = $value->__toArray();
                    } elseif (method_exists($value,'toArray')) {
                        $value = $value->toArray();
                    } else {
                        $value = (array) $value;
                    }
                case 'array':
                    $output = array_merge($output, array_flatten($value, $compositeKey));
                    break;
                default:
                    $output[$compositeKey] = $value;
                    break;
            }
        }
        return $output;
    }
}

if(!function_exists('random_character')) {
    /**
     * Returns a random character from the given alphabet
     *
     * @param string $alphabet
     * @return string
     */
    function random_character( $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
        return substr($alphabet,rand(0,strlen($alphabet)-1),1);
    }
}

if(!function_exists('random_string')) {
    /**
     * Returns a string of random characters based on the (given) alphabet
     *
     * @param int $length
     * @param string $alphabet
     * @return string
     */
    function random_string( $length = 8, $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789') {
        return implode(array_map('random_character',array_fill(0,$length,$alphabet)));
    }
}

if (!function_exists('breakpoint')) {
    function breakpoint( $key, $dumpval ) {
        // TODO: only in DEV mode
        $params = array_merge($_GET,$_POST);
        if( isset($params['break']) && $params['break'] == $key ) {
            header('Content-Type: text/plain');
            var_dump($dumpval);
            die();
        }
    }
}