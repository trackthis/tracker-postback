<?php

namespace Tracker\Translator\Transform;

interface TransformInterface {

    /**
     * Handles the actual transforming of a field
     *
     * @param array  $argv
     * @param string $value
     * @return mixed
     */
    public function handle( $argv, $value );

    /**
     * Answers with the transform's name to use
     *
     * @return string
     */
    public static function getName();
}