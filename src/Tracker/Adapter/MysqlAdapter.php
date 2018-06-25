<?php

namespace Tracker\Adapter;

use PicoDb\Database;
use PicoDb\UrlParser;

class MysqlAdapter extends AbstractAdapter {

    /**
     * @var Database
     */
    protected $db = null;

    /**
     * MysqlAdapter constructor.
     * @param string $uri
     */
    public function __construct($uri) {
        // Initialize database connection
        $this->db = new Database(UrlParser::getInstance()->getSettings($uri));
    }

    /**
     * @inheritdoc
     */
    public function write($processedRecord) {

        // Make sure we have the %table%
        if(!isset($processedRecord['%table%'])) {
            return "The record did not contain a target table";
        }

        // Check if the table exists
        $sql   = trim(sprintf("SELECT 1 FROM %s", $this->db->escapeIdentifier($processedRecord['%table%'])));
        try {
            $error = intval($this->db->execute($sql)->errorCode());
            if ($error) return "Given table does not exist";
        } catch( \Exception $e ) {
            return "Given table does not exist";
        }





        var_dump($processedRecord);
        die();
    }

}