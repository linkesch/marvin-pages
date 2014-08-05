<?php

namespace Marvin\Pages\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class FrontendServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['menu'] = function () use ($app) {
            $pages = $app['db']->fetchAll("SELECT id, name, slug FROM page ORDER BY sort ASC");

            return $pages;
        };
    }

    public function boot(Application $app)
    {
    }
}
