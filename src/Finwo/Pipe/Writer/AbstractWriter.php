<?php

namespace Finwo\Pipe\Writer;

use Finwo\Pipe\Target;

abstract class AbstractWriter implements WriterInterface {
    public function __invoke( $chunk, Target $target ) {
        $target->write($this->write($chunk));
    }
}