<?php

// DEBUG function
// outputs yaml-like structure
if(!function_exists('prnt')) {
    function prnt( $data, $ret = false, $prefix = '' ) {
        $output = '';
        foreach ( $data as $key => $value ) {
            $output .= $prefix . $key . ':';
            switch(gettype($value)) {
                case 'boolean':
                    $output .= ' ' . ($value?'true':'false') . PHP_EOL;
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

require __DIR__ . '/../src/app.php';
