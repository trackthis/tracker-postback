<?php

namespace Tracker\Adapter;

use PicoDb\Database;
use PicoDb\UrlParser;
use Tracker\Translator\Translator;

class BufferAdapter extends AbstractAdapter {

    /**
     * @var Database
     */
    protected $originalTarget = null;

    /**
     * MysqlAdapter constructor.
     * @param string $uri
     * @param array  $options
     */
    public function __construct($uri) {
        $this->originalTarget = $uri;
    }

    /**
     * This prevents translation to ensure just-in-time auth calculation works
     *
     * @inheritdoc
     */
    public function record($bareRecord) {
        return $this->write($bareRecord);
    }

    /**
     * @inheritdoc
     */
    public function write($processedRecord) {
        global $_SERVICE;
        $odm = $_SERVICE['odm'];
        $mappings = array();

        // Fetch mapping from token & strip map/token ids
        if(isset($_REQUEST['auth']['token'])&&isset($_REQUEST['auth']['token']['mappings'])) {
            $mappings = array_map(function($mapping) {
                return array(
                    'source'    => $mapping['source'],
                    'translate' => $mapping['translate'],
                    'field'     => $mapping['field'],
                );
            }, $_REQUEST['auth']['token']['mappings']);
        }

        // Insert into buffer
        return !$odm->table('buffer')->insert(array(
            'target'   => $this->originalTarget,
            'mappings' => json_encode($mappings),
            't_create' => time(),
            't_try'    => 0,
            'data'     => jsoN_encode($processedRecord),
        ));
    }

}