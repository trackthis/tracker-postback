<?php

namespace Tracker\Translator\Transform;

class Ip2Long implements TransformInterface {
    public function handle($argv,$value) {
        if(count($argv)) { $value = implode(' ',$argv); }
        return ip2long($value) ?: 0;
    }
    public static function getName() {
        return 'ip2long';
    }
}