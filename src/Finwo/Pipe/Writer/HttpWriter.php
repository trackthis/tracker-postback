<?php

namespace Finwo\Pipe\Writer;

class HttpWriter extends AbstractWriter {
    protected $target;

    public function __construct($target) {
        $this->target = $target;
    }

    public function write($data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->target);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        $output  = curl_exec($ch);
        $info    = curl_getinfo($ch);
        curl_close($ch);
        if($info['http_code']!==200) return false;

        // Detect JSON ok in body
        if($info['content_type']=='application/json') {
            try {
                $response = json_decode($output,true);
                var_dump($response);
                if(key_exists('ok',$response) && (!$response['ok'])) return false;
            } catch( \Exception $e ) {
                return false;
            }
        }

        return true;
    }

}