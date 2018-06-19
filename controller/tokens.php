<?php
/** @var \Klein\Klein $router */


$router->respond('/tokens',function () {
    global $_SERVICE;

    die($_SERVICE['template']('token-overview'));
});

$router->respond('GET', '/tokens/[i:id]', function( \Klein\Request $request ) {
    global $_SERVICE;

    die($_SERVICE['template']('token-edit', array(
        'title' => 'Edit token: ' . '--[description]--',
        'edit'  => true,
        'token' => $request->param('id', false ),
    )));
});

$router->respond('GET', '/tokens/new', function() {
    global $_SERVICE;

    die($_SERVICE['template']('token-edit', array(
        'title' => 'New token',
        'new'   => true,
    )));
});
