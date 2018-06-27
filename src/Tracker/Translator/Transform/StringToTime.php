<?php

namespace Tracker\Translator\Transform;

class StringToTime implements TransformInterface {
    public function handle($argv,$value) {
        $format = 'Y-m-d H:i:s';
        if(count($argv)) { $format = implode(' ',$argv); }
        return gmdate($format,strtotime($value));
    }
    public static function getName() {
        return 'strtotime';
    }
}