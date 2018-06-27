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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $output  = curl_exec($ch);
        $info    = curl_getinfo($ch);
        curl_close($ch);
//        if($info['http_code']!==200) {
            var_dump($data);
            var_dump($info);
            var_dump($output);
            die();
//        }
        return ($info['http_code'] === 200);
    }

}