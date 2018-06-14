<?php

$_SERVICE['template'] = function ($c) {
    return new \Handlebars\Handlebars(array(
        'loader'          => new \Handlebars\Loader\FilesystemLoader(
            APPROOT . DS . 'template',
            array('extension' => 'hbs')
        ),
        'partials_loader' => new \Handlebars\Loader\FilesystemLoader(
            APPROOT . DS . 'template' . DS . 'partial',
            array('extension' => 'hbs')
        ),
    ));
};
