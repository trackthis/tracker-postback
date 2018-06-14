<?php

namespace Finwo\Framework\Config;

use Finwo\DataFile\DataFile;
use Finwo\PropertyAccessor\PropertyAccessor;

class Config
{
    /**
     * @return PropertyAccessor
     */
    protected static function getAccessor()
    {
        static $cache = null;
        if(is_null($cache)) {
            $cache = new PropertyAccessor();
        }
        return $cache;
    }

    /**
     * @return array
     */
    protected static function &getFull()
    {
        static $cache = null;
        if(is_null($cache)) {
            // Detect files
            $files = array();
            foreach(Datafile::$supported as $fileType) {
                $files = array_merge($files, glob( APPROOT . DS . 'config' . DS . '*.' . $fileType));
            }
            // Load them
            $cache = array();
            foreach($files as $file) {
                $cache = array_merge($cache, DataFile::read($file));
            }
        }
        return $cache;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public static function get( $key = null )
    {
        if(is_null($key)) {
            return self::getFull();
        }
        return self::getAccessor()->getSafe(self::getFull(), $key, '.');
    }

    /**
     * @param string $key
     * @param string $value
     */
    public static function set( $key, $value )
    {
        self::getAccessor()->set( self::getFull(), $key, $value, '.' );
    }
}
