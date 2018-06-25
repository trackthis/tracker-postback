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
        // TODO: Implement write() method.
    }

}