<?php

namespace Finwo\Pipe\Writer;

use Finwo\Pipe\Target;
use PicoDb\Database;
use PicoDb\UrlParser;

class DatabaseWriter {
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
    public function __invoke( $command, Target $target ) {
        reset($command);
        $method = strtolower(key($command));
        $data   = $command[key($command)];
        $id     = is_array($data) ? (isset($data['id'])?$data['id']:false) : $data;

        if($method=='upsert') {
            $method = $id ? 'update' : 'insert';
        }

        switch($method) {
            case 'update':
                if(!$id) return $target->write(false);
                if($this->db->table($this->table)->eq('id',$id)->update($data)) {
                    return $target->write($id);
                } else {
                    return $target->write(false);
                }
            case 'insert':
                if($this->db->table($this->table)->insert($data)) {
                    return $target->write($this->db->getLastId());
                } else {
                    return $target->write(false);
                }
            case 'delete':
                if(!$id) return $target->write(false);
                return $target->write($this->db->table($this->table)->eq('id',$id)->remove());
            default:
                return $target->write(false);
        }
    }
}