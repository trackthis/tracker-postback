<?php

namespace Finwo\Pipe\Writer;

use Finwo\Pipe\Target;

abstract class Writer {
    /**
     * @param $target
     * @return WriterInterface
     * @throws \Exception
     */
    public static function create( $target ) {
        $settings = parse_url($target);
        if(!class_exists($classname='Finwo\\Pipe\\Writer\\'.ucfirst($settings['scheme']).'Writer')) {
            throw new \Exception("Class ${classname} does not exist");
        }
        return new $classname($target);
    }

    /**
     * Data-directed targets
     *
     * Less efficient than a single writer though
     *
     * @return \Closure
     */
    public static function auto() {
        static $writers = array();
        return function( $chunk, Target $target ) use ($writers) {
            if(!isset($chunk['target'])) return;
            if(!isset($chunk['data'])) return;
            $writer = isset($writers[$chunk['target']])
                ? $writers[$chunk['target']]
                : Writer::create($chunk['target']);
            $writers[$chunk['target']] = $writer;
            $target->write($writer->write($chunk['data']));
        };
    }
}