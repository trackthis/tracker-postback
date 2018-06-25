<?php

namespace Tracker\Translator\Transform;

interface TransformInterface {

    /**
     * Handles the actual transforming of a field
     *
     * @param string $value
     * @return mixed
     */
    public function handle( $value );

    /**
     * Answers with the transform's name to use
     *
     * @return string
     */
    public static function getName();
}