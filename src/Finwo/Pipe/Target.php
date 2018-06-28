<?php

namespace Finwo\Pipe;

class Target {
    /** @var callable */
    protected $cb = null;
    /** @var Target */
    protected $next = null;
    public $start = null;
    public function __construct( $cb ) {
        $this->cb    = $cb;
        $this->start = $this;
    }
    public function write($chunk) {
        if(is_null($this->cb)) return $this;
        if(is_null($chunk)&&(!is_null($this->next))) return $this->next->write(null);
        $cb = $this->cb;$cb($chunk,$this->next);
        return $this;
    }
    public function pipe( $cb ) {
        $this->next = new Target($cb);
        $this->next->start = $this->start;
        return $this->next;
    }
}