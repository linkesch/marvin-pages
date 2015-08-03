<?php

namespace Marvin\Pages\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;

class InstallControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        // Install
        $controllers->get('/', function () use ($app) {
            $messages = $app['install_plugins']['pages']();

            return $app['twig']->render('admin/install.twig', array(
                'messages' => $messages,
            ));
        });

        return $controllers;
    }
}
