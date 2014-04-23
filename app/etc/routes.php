<?php

$router = new Phalcon\Mvc\Router( FALSE );

// automatically remove trailing slashes
//
$router->removeExtraSlashes( TRUE );

// base application
//
$router->add(
    '/:controller/:action/:params',
    array(
        'namespace' => 'Controllers',
        'controller' => 1,
        'action' => 2,
        'params' => 3
    ));
$router->add(
    '/:controller[/]{0,1}',
    array(
        'namespace' => 'Controllers',
        'controller' => 1
    ));

// load the namespaces, array takes the form $segment => $namespace
//
foreach ( $config->app->modules as $segment => $namespace )
{
    $router->add(
        "/{$segment}/:controller/:action/:params",
        array(
            'namespace' => 'Controllers\\'. $namespace,
            'controller' => 1,
            'action' => 2,
            'params' => 3,
        ));
    $router->add(
        "/{$segment}/:controller[/]{0,1}",
        array(
            'namespace' => 'Controllers\\'. $namespace,
            'controller' => 1,
        ));
    $router->add(
        "/{$segment}[/]{0,1}",
        array(
            'namespace' => 'Controllers\\'. $namespace,
            'controller' => 'index',
            'action' => 'index'
        ));
}

// posts routes
//
$router->add(
    "/([0-9]{4})/([0-9]{2})/([a-zA-Z0-9_-]+)",
    array(
        'namespace' => 'Controllers',
        'controller' => 'posts',
        'action' => 'show',
        'year' => 1,
        'month' => 2,
        'slug' => 3
    ));

return $router;