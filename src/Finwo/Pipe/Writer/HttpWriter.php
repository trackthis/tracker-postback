<?php

namespace Finwo\Pipe\Writer;

class HttpWriter extends AbstractWriter {
    protected $target;
    protected $options = array();

    public function __construct($target, $options = null) {
        $this->target = $target;
        if(!is_null($options)) $this->options = $options;
    }

    public function write($data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->target);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

        // Timeout option
        if(isset($this->options['timeout'])) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, intval($this->options['timeout']));
            curl_setopt($ch, CURLOPT_TIMEOUT, intval($this->options['timeout']) * 2);
        }

        $output  = curl_exec($ch);
        $info    = curl_getinfo($ch);
        curl_close($ch);
        if($info['http_code']!==200) return false;

        // Detect JSON ok in body
        if($info['content_type']=='application/json') {
            try {
                $response = json_decode($output,true);
                if(key_exists('ok',$response) && (!$response['ok'])) return false;
            } catch( \Exception $e ) {
                return false;
            }
        }

        return true;
    }

}