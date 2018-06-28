<?php

namespace Tracker\Translator\Transform;

class MD5 implements TransformInterface {
    public function handle($argv,$value) {
        if(count($argv)) { $value = implode(' ',$argv); }
        return md5($value);
    }
    public static function getName() {
        return 'md5';
    }
}