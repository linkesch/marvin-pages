<?php

namespace Marvin\Pages\Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;


class AdminControllerProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];

        $controllers->get('/', function () use ($app) {
            $pages = $app['db']->fetchAll("SELECT * FROM page ORDER BY sort ASC");

            return $app['twig']->render('admin/pages/list.twig', array(
                'pages' => $pages,
            ));
        })
        ->bind('admin_pages');

        $controllers->match('/form/{id}', function (Request $request, $id) use ($app) {
            $pageData = array();

            if($id > 0)
            {
                $pageData = $app['db']->fetchAssoc("SELECT * FROM page WHERE id = ?", array($id));
            }

            $form = $app['form.factory']->createBuilder('form', $pageData)
                ->add('id', 'hidden')
                ->add('name', 'text')
                ->add('content', 'textarea', array(
                    'required' => false,
                ))
                ->getForm();

            $form->handleRequest($request);

            if($form->isValid())
            {
                $data = $form->getData();

                $slug = $originalSlug = $app['slugify']->slugify($data['name']);
                $i = 2;
                do
                {
                    $find = $app['db']->fetchAssoc("SELECT COUNT(*) AS count FROM page WHERE slug = ?". ($data['id'] > 0 ? " AND id != ". $data['id'] : ""), array($slug));
                    if($find['count'] > 0)
                    {
                        $slug = $originalSlug .'-'. $i;
                        $i++;
                    }
                }
                while($find['count'] > 0);

                if($data['id'] == 0)
                {
                    $maxSort = $app['db']->fetchAssoc("SELECT MAX(sort) AS sort FROM page");
                    $app['db']->executeUpdate("INSERT INTO page (name, slug, content, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)", array(
                        $data['name'],
                        $slug,
                        $data['content'],
                        $maxSort['sort']+1,
                        date('Y-m-d H:i:s'),
                        date('Y-m-d H:i:s'),
                    ));

                    $app['session']->getFlashBag()->add('message', 'The new page was added');
                }
                else
                {
                    $app['db']->executeUpdate("UPDATE page SET name = ?, slug = ?, content = ?, updated_at = ? WHERE id = ?", array(
                        $data['name'],
                        $slug,
                        $data['content'],
                        date('Y-m-d H:i:s'),
                        $data['id'],
                    ));

                    $app['session']->getFlashBag()->add('message', 'The page was changed');
                }

                return $app->redirect('/admin/pages');
            }

            return $app['twig']->render('admin/pages/form.twig', array(
                'form' => $form->createView(),
            ));
        })
        ->value('id', 0)
        ->assert('id', '\d+');

        $controllers->get('/delete/{id}', function ($id) use ($app) {
            $page = $app['db']->fetchAssoc("SELECT sort FROM page WHERE id = ?", array($id));
            $app['db']->executeUpdate("UPDATE page SET sort=sort-1 WHERE sort > ?", array($page['sort']));

            $app['db']->delete('page', array('id' => $id));

            $app['session']->getFlashBag()->add('message', 'The page was deleted');
            return $app->redirect('/admin/pages');
        })
        ->assert('id', '\d+');

        $controllers->match('/move/{id}/{type}', function ($id, $type) use ($app) {
            $action = $type == 'down' ? 1 : -1;

            $page = $app['db']->fetchAssoc("SELECT sort FROM page WHERE id = ?", array($id));
            $app['db']->executeUpdate("UPDATE page SET sort=sort+? WHERE sort = ?", array(-$action, $page['sort']+$action));
            $app['db']->executeUpdate("UPDATE page SET sort=sort+? WHERE id = ?", array($action, $id));

            $app['session']->getFlashBag()->add('message', 'Order of pages was changed');
            return $app->redirect('/admin/pages');
        })
        ->assert('id', '\d+')
        ->assert('type', '(up|down)');

        return $controllers;
    }
}
