<?php

namespace Marvin\Pages\Provider;

use Silex\Application;
use Silex\ServiceProviderInterface;

class InstallServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app->extend('install_plugins', function ($plugins) use ($app) {

            $plugins[] = function () use ($app) {

                $sm = $app['db']->getSchemaManager();
                $schema = new \Doctrine\DBAL\Schema\Schema();

                // Create table page
                $pageTable = $schema->createTable('page');
                $pageTable->addColumn('id', 'integer', array("autoincrement" => true));
                $pageTable->addColumn('name', 'string');
                $pageTable->addColumn('slug', 'string');
                $pageTable->addColumn('content', 'text', array('notnull' => false));
                $pageTable->addColumn('sort', 'integer', array('notnull' => false));
                $pageTable->addColumn('created_at', 'datetime');
                $pageTable->addColumn('updated_at', 'datetime');
                $pageTable->setPrimaryKey(array("id"));
                $pageTable->addUniqueIndex(array("slug"));
                $sm->createTable($pageTable);

                $messages[] = $app['install_status'](
                    $sm->tablesExist(array('page')),
                    'Page table was created.',
                    'Problem creating page table.'
                );

                // Create homepage
                $app['db']->executeUpdate("INSERT INTO page (name, slug, content, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)", array(
                    'Home',
                    'home',
                    '<p>Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Donec sed odio dui. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit. Etiam porta sem malesuada magna mollis euismod. Cras justo odio, dapibus ac facilisis in, egestas eget quam.</p>',
                    1,
                    date('Y-m-d H:i:s'),
                    date('Y-m-d H:i:s'),
                ));

                $home = $app['db']->fetchAssoc("SELECT COUNT(*) AS count FROM page WHERE name = 'Home'");
                $messages[] = $app['install_status'](
                    $home['count'],
                    'Homepage was created.',
                    'Problem creating homepage.'
                );

                return $messages;
            };

            return $plugins;
        });
    }

    public function boot(Application $app)
    {
    }
}
