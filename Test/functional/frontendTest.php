<?php

use Marvin\Core\Test\FunctionalTestCase;

class frontendTest extends FunctionalTestCase
{
    public function testPagesList()
    {
        $client = $this->createClient();
        $crawler = $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }
}
