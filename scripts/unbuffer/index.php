<?php

use Finwo\Framework\Config\Config;
use Finwo\Pipe\Reader\DatabaseReader;
use Finwo\Pipe\Target;
use Finwo\Pipe\Writer\DatabaseWriter;
use Finwo\Pipe\Writer\Writer;

// Helper function
if(!function_exists('db2uri')) {
    function db2uri( $settings ) {
        if(is_string($settings)) return $settings;
        return build_url(array(
            'scheme' => isset($settings['driver'])   ? $settings['driver']         : null,
            'user'   => isset($settings['username']) ? $settings['username']       : null,
            'pass'   => isset($settings['password']) ? $settings['password']       : null,
            'host'   => isset($settings['hostname']) ? $settings['hostname']       : null,
            'port'   => isset($settings['port'])     ? $settings['port']           : null,
            'path'   => isset($settings['database']) ? ('/'.$settings['database']) : null,
        ));
    }
}

// Start with the database
return (new Target(new DatabaseReader(db2uri(Config::get('database')) . '?{"sort":{"t_try":"asc"}}')))

    // EOF catch
    ->pipe(function( $row, Target $target ) {
        var_dump($row);
        $target->write($row);
    })

    // Translate the data & prepare for auto-writer
    ->pipe(function( $row, Target $target ) {
        $mappings   = json_decode($row['mappings'],true);
        $translator = new \Tracker\Translator\Translator($mappings);
        $target->write(array(
            'data'     => $translator->translate(json_decode($row['data'],true)),
            'original' => $row,
            'target'   => $row['target'],
        ));
    })

    // Write to target
    ->pipe(Writer::auto($params))

    // True-like response = success
    // Else: update t_try
    ->pipe(function( $chunk, Target $target) {
        if($chunk['response']) {
            $target->write(array('delete'=>$chunk['chunk']['original']['id'],));
        } else {
            $row = $chunk['chunk']['original'];
            $row['t_try'] = time();
            $target->write(array('update'=>$row));
        }
    })

    // Write to DB again
    ->pipe(new DatabaseWriter(getenv('DATABASE_URL'), $params))
    ;
