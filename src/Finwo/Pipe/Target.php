<?php

namespace Finwo\Pipe;

class Target {
    /** @var callable */
    protected $cb = null;
    /** @var callable */
    protected $next = null;
    public $start = null;
    public function __construct( $cb ) {
        $this->cb    = $cb;
        $this->start = $this;
    }
    public function write($chunk) {
        if(is_null($this->cb)) return $this;
        $cb = $this->cb;$cb($chunk,$this->next);
        return $this;
    }
    public function pipe( $cb ) {
        $this->next = new Target($cb);
        $this->next->start = $this->start;
        return $this->next;
    }
}