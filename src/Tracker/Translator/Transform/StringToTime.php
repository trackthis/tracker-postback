<?php

namespace Tracker\Translator\Transform;

class StringToTime implements TransformInterface {
    public function handle($argv,$value) {
        return strtotime($value);
    }
    public static function getName() {
        return 'strtotime';
    }
}