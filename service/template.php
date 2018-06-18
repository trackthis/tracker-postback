<?php

$_SERVICE['template'] = function ($c) {
    return function ($name, $data = array()) {

        static $engine = null;
        if (is_null($engine)) {
            $engine = new \Handlebars\Handlebars(array(
                'loader'          => new \Handlebars\Loader\FilesystemLoader(
                    APPROOT . DS . 'template',
                    array('extension' => 'hbs')
                ),
                'partials_loader' => new \Handlebars\Loader\FilesystemLoader(
                    APPROOT . DS . 'template' . DS . 'partial',
                    array('extension' => 'hbs')
                ),
            ));
        }


        if ('array' !== gettype($data)) $data = array();
        $data['__pageName'] = $name;
        return $engine->render($name, $data);
    };
};
