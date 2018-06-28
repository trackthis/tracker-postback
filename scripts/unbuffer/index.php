<?php

if (!getenv('DATABASE_URL')) {
    putenv('DATABASE_URL=mysql://user:pass@localhost/postbacktracker/buffer');
}

use Finwo\Pipe\Reader\DatabaseReader;
use Finwo\Pipe\Target;
use Finwo\Pipe\Writer\DatabaseWriter;
use Finwo\Pipe\Writer\Writer;

// Start with the database
return (new Target(new DatabaseReader(getenv('DATABASE_URL') . '?{"sort":{"t_try":"asc"}}')))

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
