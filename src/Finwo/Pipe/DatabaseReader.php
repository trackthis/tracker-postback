<?php

namespace Finwo\Pipe;

use PicoDb\Database;
use PicoDb\UrlParser;

class DatabaseReader {
    protected $db;
    protected $table;
    protected $query = array();
    /**
     * @param string $source
     * @throws \Exception
     */
    public function __construct( $source ) {
        $settings = parse_url($source);
        $path     = explode('/',trim($settings['path'],'/'));
        if(count($path)===1) array_push($path,'buffer');
        if(count($path)!==2) throw new \Exception("Invalid source param");
        $this->table      = array_pop($path);
        $settings['path'] = '/'.array_shift($path);
        if(isset($settings['query'])) {
            $this->query       = json_decode($settings['query'],true);
            $settings['query'] = null;
        }
        $source           = build_url($settings);
        $this->db         = new Database(UrlParser::getInstance()->getSettings($source));
    }
    public function __invoke( $chunk, Target $target ) {
        $q = $this->db->table($this->table);
        if(isset($this->query['eq'])) {
            foreach ($this->query['eq'] as $key => $value) $q = $q->eq($key,$value);
            foreach ($this->query['sort'] as $key => $value) $q = $q->$value($key);
        }
        $row = $q->findOne();
//        if(!is_null($row)) {
//            $this->db->table($this->table)->eq('id',$row['id'])->remove();
//        }
        $target->write($row);
    }
}