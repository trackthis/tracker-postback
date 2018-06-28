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
        $table = $processedRecord['%table%'];
        $sql   = trim(sprintf("SELECT 1 FROM %s", $this->db->escapeIdentifier($table)));
        try {
            $error = intval($this->db->execute($sql)->errorCode());
            if ($error) return "Given table does not exist";
        } catch( \Exception $e ) {
            return "Given table does not exist";
        }

        // Remove nulls
        foreach ($processedRecord as $key => $value) {
            if(is_null($value)) {
                unset($processedRecord[$key]);
            }
        }

        // Let's write then
        unset($processedRecord['%table%']);
        try {
            $result = $this->db->table($table)->insert($processedRecord);
            if (!$result) {
                return 'idx_unique';
            }
            return 0;
        } catch( \Exception $e ) {
            return $e->getMessage();
        }
    }

}