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
                'helpers' => new \Handlebars\Helpers(array(
                    'implode' => function() {
                        var_dump(func_get_args());
                        return '';
                    }
                ))
            ));
        }


        if ('array' !== gettype($data)) $data = array();
        $data['__pageName'] = $name;
        $data['__query']    = '';
        $params             = explode('?', $_SERVER['REQUEST_URI'], 2);
        array_shift($params);
        if(count($params)) $data['__query'] = array_shift($params);

        return $engine->render($name, $data);
    };
};
