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
                    'json' => function( \Handlebars\Template $template, \Handlebars\Context $context, \Handlebars\Arguments $arguments, $block ) {
                        return json_encode($context->get($arguments->getPositionalArguments()[0]));
                    },
                    'date' => function( \Handlebars\Template $template, \Handlebars\Context $context, \Handlebars\Arguments $arguments, $block ) {
                        return date('c', $context->get($arguments->getPositionalArguments()[0]));
                    },
                    'for' => function( \Handlebars\Template $template, \Handlebars\Context $context, \Handlebars\Arguments $arguments, $block ) {
                        $ctx  = $context->get('this'); $out  = '';
                        $list = $context->get($arguments->getPositionalArguments()[0]);
                        $args = function( $index, $entry ) use ($ctx) {
                            return array_merge($ctx,$entry,array('@index'=>$index));
                        };
                        if ( $arguments->getPositionalArguments()[1] === 'as' ) {
                            $name = $arguments->getPositionalArguments()[2];
                            $args = function( $index, $entry ) use ( $ctx, $name ) {
                                return array_merge( $ctx, array( '@index' => $index, $name => $entry ));
                            };
                        }
                        foreach ($list as $i => $entry) {
                            $out .= $template->render($args($i,$entry));
                        }
                        return $out;
                    }
                ))
            ));
        }


        if ('array' !== gettype($data)) $data = array();
        $query              = array_merge($_GET,$_POST);
        $data['__pageName'] = $name;
        $data['__query']    = '';
        $data['__token']    = isset($query['token']) ? $query['token'] : '';
        $data['__auth']     = isset($_REQUEST['auth']) ? $_REQUEST['auth'] : false;
        $data['__settings'] = $data['__auth'] ? $data['__auth']['account']['settings'] : false;
        $data['GET']        = $_GET;
        $data['POST']       = $_POST;
        $params             = explode('?', $_SERVER['REQUEST_URI'], 2);
        array_shift($params);
        if(count($params)) $data['__query'] = array_shift($params);

        return $engine->render($name, $data);
    };
};
