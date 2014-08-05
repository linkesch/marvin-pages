<?php

namespace Marvin\Pages\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


class FrontendControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/{slug}', function ($slug) use ($app) {
            if($slug)
            {
                $page = $app['db']->fetchAssoc("SELECT * FROM page WHERE slug = ?", array($slug));
            }
            else
            {
                $page = $app['db']->fetchAssoc("SELECT * FROM page ORDER BY sort ASC LIMIT 0,1");
            }

            return $app['twig']->render($app['config']['theme'] .'/page.twig', array(
                'page' => $page,
            ));

        })
        ->value('slug', null)
        ->assert('slug', '(?!admin|install|login)([a-z0-9-_]+)')
        ->bind('page');

        return $controllers;
    }
}
