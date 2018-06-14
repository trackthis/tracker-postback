<?php

$_SERVICE['odm'] = function($c) {
    return new \PicoDb\Database(Finwo\Framework\Config\Config::get('database'));
};
