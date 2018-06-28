<?php

namespace Finwo\Pipe\Writer;

use Finwo\Pipe\Target;

interface WriterInterface {
    public function __construct( $target, $options = null );
    public function __invoke( $chunk, Target $target );
    /** @return bool|string */
    public function write( $data );
}