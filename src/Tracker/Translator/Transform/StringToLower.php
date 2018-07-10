<?php

namespace Tracker\Translator\Transform;

class StringToLower implements TransformInterface {
    public function handle($argv,$value) {
        if(count($argv)) { $value = implode(' ',$argv); }
        return mb_strtolower($value);
    }
    public static function getName() {
        return 'strtolower';
    }
}