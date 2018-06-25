<?php

namespace Tracker\Adapter;

interface AdapterInterface {

    /**
     * AdapterInterface constructor
     *
     * Sets up the adapter, including making connections to databases etc.
     *
     * @param string $uri
     */
    public function __construct( $uri );

    /**
     * Set the fields to handle
     *
     * @param array $mappings
     * @return boolean
     * @throws \Exception
     */
    public function setFields( $mappings );

    /**
     * Insert a new record
     *
     * Runs the given record through the translator & hands it over to the target
     *
     * @param array $bareRecord
     * @return boolean
     * @throws \Exception
     */
    public function record( $bareRecord );

    /**
     * Write a record to the target
     *
     * This is called from the record function to actually pass it to the target
     *
     * @param $processedRecord
     * @return boolean
     */
    public function write( $processedRecord );
}