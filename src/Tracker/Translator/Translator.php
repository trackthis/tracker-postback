<?php

namespace Tracker\Translator;

use Tracker\Translator\Transform\Fallback;
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
            Fallback::getName()     => new Fallback(),
            Ip2Long::getName()      => new Ip2Long(),
            Optional::getName()     => new Optional(),
            StringToTime::getName() => new StringToTime(),
        );

        // Let's home they're all correct
        $this->mappings = $mappings;
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

            // No translate = use value directly
            if( (!is_string($mapping['translate'])) || (!strlen($mapping['translate'])) ) {
                $output[$field] = $value;
                continue;
            }

            // Handle transformer functions
            if ( isset($mapping['translate']) && (substr($mapping['translate'],0,1)=='%') ) {
                $argv        = str_getcsv(substr($mapping['translate'],1)," ");
                $transformer = array_shift($argv);
                if(isset($this->transforms[$transformer])) {
                    $output[$field] = $this->transforms[$transformer]->handle($argv,$value);
                    continue;
                }
            }

            // Actual mapping
            // Handles: "origin=target"
            // Handles: "DC|*=newValue"
            // Handles: "DC|{user}|{session}"
            $maps = str_getcsv($mapping['translate'],"&");
            $org  = $value;
            if(count($maps)) {
                $value = null;
            }
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
                if ( (substr($filter,0,1)==='/') ) {
                    if (!preg_match($filter,$org)) {
                        continue;
                    }
                } elseif ( $filter !== $org ) {
                    continue;
                }

                // Write the new value
                $value = string_format($format,array_merge($record,$output));
            }

            // Write it to the output field
            $output[$field] = $value;
        }

        return $output;
    }
}