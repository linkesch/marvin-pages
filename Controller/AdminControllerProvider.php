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

                    $data['id'] = $app['db']->lastInsertId();

                    $app['session']->getFlashBag()->add('message', 'The new page was added.');
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

                    $app['session']->getFlashBag()->add('message', 'Changes were saved.');
                }

                return $app->redirect('/admin/pages/form/'. $data['id']);
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

        $controllers->match('/file/upload', function (Request $request) use ($app) {
            $file = $request->files->get('file');

            if($file)
            {
                if(file_exists($app['config']['upload_dir']) == false)
                {
                    mkdir($app['config']['upload_dir']);
                }

                $file->move($app['config']['upload_dir'], $file->getClientOriginalName());

                return $app['config']['public_upload_dir'] .'/'. $file->getClientOriginalName();
            }
        });

        $controllers->match('/file/delete', function (Request $request) use ($app) {
            $file = $request->get('file');

            if(file_exists($app['config']['web_dir'] .'/'. $file) && dirname($file) == $app['config']['public_upload_dir'])
            {
                unlink($app['config']['web_dir'] .'/'. $file);
            }
        });

        return $controllers;
    }
}
