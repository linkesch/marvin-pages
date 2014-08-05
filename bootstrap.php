<?php

// Register service provider
$app->register(new Marvin\Pages\Provider\InstallServiceProvider());
$app->register(new Marvin\Pages\Provider\FrontendServiceProvider());


// Require plugin middlewares
//require __DIR__ .'/middlewares.php';


// Mount plugin controller provider
$app->mount('/admin/pages', new Marvin\Pages\Controller\AdminControllerProvider());
$app->mount('/', new Marvin\Pages\Controller\FrontendControllerProvider());
