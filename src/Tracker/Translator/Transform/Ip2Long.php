<?php

namespace Tracker\Translator\Transform;

class Ip2Long implements TransformInterface {
    public function handle($value) {
        return ip2long($value);
    }
    public static function getName() {
        return 'ip2long';
    }
}