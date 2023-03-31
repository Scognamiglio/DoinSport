<?php
namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookBoTest extends WebTestCase
{
    public function testBook()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/book');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('a', 'DoinSport');
    }

}