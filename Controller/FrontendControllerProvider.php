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

        $controllers->get('/{slug}', function (Request $request, $slug) use ($app) {
            if ($slug) {
                $page = $app['db']->fetchAssoc("SELECT * FROM page WHERE slug = ?", array($slug));
            } else {
                $page = $app['db']->fetchAssoc("SELECT * FROM page ORDER BY sort ASC LIMIT 0,1");
                $request->query->set('slug', $page['slug']);
            }

            if (!$page) {
                $app->abort(404, 'Page "'. $slug .'" does not exist.');
            }

            // Other plugins
            $pagesPlugins = array();
            foreach ($app['config']['plugins'] as $plugin) {
                if ($plugin != 'pages' && isset($app['pages_plugins'][$plugin])) {
                    $pagesPlugins[$plugin] = $app['pages_plugins'][$plugin]($page['id']);
                }
            }

            return $app['twig']->render($app['config']['theme'] .'/page.twig', array(
                'page' => $page,
                'pagesPlugins' => $pagesPlugins,
            ));

        })
        ->value('slug', null)
        ->assert('slug', '(?!admin|install|login)([a-z0-9-_]+)')
        ->bind('page');

        return $controllers;
    }
}
