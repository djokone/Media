<?php
use Cake\Routing\Router;

Router::plugin('Media', function ($routes) {
	$routes->connect(
        '/media/index/:ref/:ref_id',
        [
            'controller' => 'medias',
            'action' => 'index',
        ],
        [
            'pass' => [
            	'ref',
                'ref_id'
            ]
        ]
        );
    $routes->fallbacks();
});