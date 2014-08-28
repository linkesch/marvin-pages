<?php

use Marvin\Marvin\Test\FunctionalTestCase;

class FrontendTest extends FunctionalTestCase
{
    public function testPagesList()
    {
        $client = $this->createClient();
        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isOk());
    }
}
