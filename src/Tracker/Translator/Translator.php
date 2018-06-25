<?php

namespace Tracker\Translator;

use Tracker\Translator\Transform\Optional;
use Tracker\Translator\Transform\StringToTime;

class Translator {

    /**
     * @var array
     */
    protected $mappings = null;

    /**
     * Custom translator functions
     *
     * @var array
     */
    protected $transforms = null;

    /**
     * Translator constructor.
     *
     * Handles mappings
     *
     * @param array $mappings
     *
     * @throws \Exception
     */
    public function __construct( $mappings ) {

        // Ensure the mappings var is an array
        if (gettype($mappings) !== 'array') {
            throw new \Exception("Given mappings not an array");
        }

        // Initialize the transforms
        $this->transforms = array(
            Optional::getName()     => new Optional(),
            StringToTime::getName() => new StringToTime(),
        );
    }

    /**
     * Translate a record
     *
     * Translates a record according to the previously given mappings
     *
     * @param array $record
     *
     * @return array
     * @throws \Exception
     */
    public function translate( $record ) {
        $output = array();
        foreach ($this->mappings as $mapping) {
            if(!isset($mapping['field'])) continue;
            $field = $mapping['field'];

        }
        return $output;
    }
}