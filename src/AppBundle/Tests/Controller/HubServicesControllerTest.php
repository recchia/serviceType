<?php

namespace AppBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HubServicesControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/index');
    }

    public function testProcess()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/process');
    }

    public function testData()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/data');
    }

}
