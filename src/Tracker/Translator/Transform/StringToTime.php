<?php

namespace Tracker\Translator\Transform;

class StringToTime implements TransformInterface {
    public function handle($argv,$value) {
        return gmdate('Y-m-d H:i:s',strtotime($value));
    }
    public static function getName() {
        return 'strtotime';
    }
}