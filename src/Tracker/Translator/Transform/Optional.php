<?php

namespace Tracker\Translator\Transform;

class Optional implements TransformInterface {
    public function handle($argv,$value) {
        return $value ? $value : '';
    }
    public static function getName() {
        return 'optional';
    }
}