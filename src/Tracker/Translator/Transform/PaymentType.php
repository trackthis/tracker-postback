<?php

namespace Tracker\Translator\Transform;

class PaymentType implements TransformInterface {

    protected $map = array(
        'ideal'      => 10,
        'creditcard' => 11,
        'bancontact' => 436,
    );

    public function handle($argv,$value) {
        if(count($argv)) { $value = implode(' ',$argv); }
        if(isset($this->map[$value])) return $this->map[$value];
        if(is_numeric($value)) return intval($value);
        return null;
    }
    public static function getName() {
        return 'paymenttype';
    }
}
