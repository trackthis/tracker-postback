<?php

namespace Finwo\Pipe\Writer;

use Finwo\Pipe\Target;

abstract class Writer {
    /**
     * @param string $target
     * @param array  $options
     * @return WriterInterface
     * @throws \Exception
     */
    public static function create( $target, $options = null ) {
        $settings = parse_url($target);
        if(!class_exists($classname='Finwo\\Pipe\\Writer\\'.ucfirst($settings['scheme']).'Writer')) {
            throw new \Exception("Class ${classname} does not exist");
        }
        return new $classname($target, $options);
    }

    /**
     * Data-directed targets
     *
     * Less efficient than a single writer though
     *
     * @param array $options
     *
     * @return \Closure
     */
    public static function auto( $options = null ) {
        static $writers = array();
        return function( $chunk, Target $target ) use ($writers,$options) {
            if(!isset($chunk['target'])) return;
            if(!isset($chunk['data'])) return;
            $writer = isset($writers[$chunk['target']])
                ? $writers[$chunk['target']]
                : Writer::create($chunk['target'], $options);
            $writers[$chunk['target']] = $writer;
            $target->write(array(
                'chunk'    => $chunk,
                'response' => $writer->write($chunk['data']),
            ));
        };
    }
}