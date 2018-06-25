<?php

namespace Tracker\Translator\Transform;

class Optional implements TransformInterface {
    public function handle($value) {
        return $value ? $value : '';
    }
    public static function getName() {
        return 'optional';
    }
}