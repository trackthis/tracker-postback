<?php

namespace Tracker\Translator;

use Tracker\Translator\Transform\Ip2Long;
use Tracker\Translator\Transform\Optional;
use Tracker\Translator\Transform\StringToTime;
use Tracker\Translator\Transform\TransformInterface;

class Translator {

    /**
     * @var array
     */
    protected $mappings = null;

    /**
     * Custom translator functions
     *
     * @var TransformInterface[]
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
            Ip2Long::getName()      => new Ip2Long(),
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

            // Make sure we need to write it
            if(!isset($mapping['field'])) continue;
            $field = $mapping['field'];
            $value = null;

            // Fetch from source
            if ( isset($mapping['source']) && strlen($mapping['source']) ) {
                $value = isset($record[$mapping['source']]) ? $record[$mapping['source']] : null;
            }

            // Handle transformer functions
            if ( isset($mapping['translate']) && (substr($mapping['translate'],0,1)=='%') ) {
                $argv        = str_getcsv(substr($mapping['translate'],1)," ");
                $transformer = array_shift($tokens);
                if(isset($this->transforms[$transformer])) {
                    $value = $this->transforms[$transformer]->handle($argv,$value);
                }
            }

            // Actual mapping
            // Handles: "origin=target"
            // Handles: "DC|*=newValue"
            // Handles: "DC|{user}|{session}"
            if (strpos($mapping['translate'],'&')!==false) {
                $maps = str_getcsv($mapping['translate'],"&");
                foreach ($maps as $map) {

                    // Split filter & format
                    $parts = str_getcsv($map,"=");
                    if(count($parts)==1) array_unshift($parts,"/.*/");
                    $filter = array_shift($parts);
                    $format = implode('=',$parts);

                    // Filter may be glob
                    if( (substr($filter,0,1)!=='/') && (strpos($filter,'*')!==false) ) {
                        $filter = '/'.str_replace('*','.*',$filter).'/';
                    }

                    // Filter may be regex
                    if ((substr($filter,0,1)==='/')) {
                        if(!preg_match($filter,$value)) continue;
                        $value = string_format($format,array_merge($record,$output));
                        break;
                    }

                }
            }

            // Write it to the output field
            $output[$field] = $value;
        }
        return $output;
    }
}