<?php

// Register service provider
$app->register(new Marvin\Pages\Provider\InstallServiceProvider());
$app->register(new Marvin\Pages\Provider\FrontendServiceProvider());


// Mount plugin controller provider
$app->mount('/admin/pages', new Marvin\Pages\Controller\AdminControllerProvider());
$app->mount('/', new Marvin\Pages\Controller\FrontendControllerProvider());
if ($app['debug']) {
    $app->mount('/install/pages', new Marvin\Pages\Controller\InstallControllerProvider());
}
