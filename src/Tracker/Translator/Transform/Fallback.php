<?php

namespace Tracker\Translator\Transform;

class Fallback implements TransformInterface {
    public function handle($argv,$value) {
        if(empty($value)) return $argv[0];
        return $value;
    }
    public static function getName() {
        return 'fallback';
    }
}