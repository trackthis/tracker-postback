<?php

namespace Tracker\Translator\Transform;

class PaymentType implements TransformInterface {

    protected $map = array(
        'ideal' => 10,
    );

    public function handle($argv,$value) {
        if(count($argv)) { $value = implode(' ',$argv); }
        if(isset($this->map[$value])) return $this->map[$value];
        return null;
    }
    public static function getName() {
        return 'paymenttype';
    }
}