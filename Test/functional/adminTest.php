<?php

use Marvin\Marvin\Test\FunctionalTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class adminTest extends FunctionalTestCase
{
    public function testPagesList()
    {
        $client = $this->createClient();
        $this->logIn($client);
        $crawler = $client->request('GET', '/admin/pages');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Pages")'));
    }

    public function testNewPage()
    {
        $client = $this->createClient();
        $this->logIn($client);
        $crawler = $client->request('GET', '/admin/pages/form');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("New page")'));

        $form = $crawler->selectButton('Save')->form();
        $crawler = $client->submit($form, array(
            'form[name]' => 'Test page',
        ));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(2, $crawler->filter('#pages tbody tr'));
        $this->assertEquals('Test page', $crawler->filter('table#pages tbody tr:last-child td:first-child')->text());
    }

    public function testEditPageWithExistingSlug()
    {
        $this->app['db']->executeUpdate("INSERT INTO page (name, slug, content, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)", array(
            "Test page",
            "test-page",
            "",
            1,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
        ));

        $client = $this->createClient();
        $this->logIn($client);
        $crawler = $client->request('GET', '/admin/pages/form');

        $this->assertTrue($client->getResponse()->isOk());

        $form = $crawler->selectButton('Save')->form();
        $crawler = $client->submit($form, array(
            'form[name]' => 'Test page',
        ));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(3, $crawler->filter('#pages tbody tr'));
        $this->assertEquals('Test page', $crawler->filter('table#pages tbody tr:last-child td:first-child')->text());
        $this->assertEquals('/test-page-2', $crawler->filter('table#pages tbody tr:last-child td:nth-child(2)')->text());
    }

    public function testEditPage()
    {
        $this->app['db']->executeUpdate("INSERT INTO page (name, slug, content, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)", array(
            "Test page",
            "test-page",
            "",
            1,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
        ));

        $client = $this->createClient();
        $this->logIn($client);
        $crawler = $client->request('GET', '/admin/pages/form/2');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('h1:contains("Test page")'));

        $form = $crawler->selectButton('Save')->form();
        $crawler = $client->submit($form, array(
            'form[name]' => 'Test page 2',
        ));

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(2, $crawler->filter('#pages tbody tr'));
        $this->assertEquals('Test page 2', $crawler->filter('table#pages tbody tr:last-child td:first-child')->text());
    }

    public function testDeletePage()
    {
        $this->app['db']->executeUpdate("INSERT INTO page (name, slug, content, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)", array(
            "Test page",
            "test-page",
            "",
            2,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
        ));

        $client = $this->createClient();
        $this->logIn($client);
        $crawler = $client->request('GET', '/admin/pages/delete/1');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertCount(1, $crawler->filter('#pages tbody tr'));
        $page = $this->app['db']->fetchAssoc("SELECT sort FROM page WHERE id = 2");
        $this->assertEquals(1, $page['sort']);
    }

    public function testMovePage()
    {
        $this->app['db']->executeUpdate("INSERT INTO page (name, slug, content, sort, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)", array(
            "Test page",
            "test-page",
            "",
            2,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s'),
        ));

        $client = $this->createClient();
        $this->logIn($client);
        $crawler = $client->request('POST', '/admin/pages/move/1/down');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals('Test page', $crawler->filter('table#pages tbody tr:first-child td:first-child')->text());
        $this->assertEquals('Home', $crawler->filter('table#pages tbody tr:last-child td:first-child')->text());


        $crawler = $client->request('POST', '/admin/pages/move/1/up');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals('Home', $crawler->filter('table#pages tbody tr:first-child td:first-child')->text());
        $this->assertEquals('Test page', $crawler->filter('table#pages tbody tr:last-child td:first-child')->text());
    }

}
