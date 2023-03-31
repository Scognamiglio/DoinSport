<?php
namespace App\Tests\Functional;

use App\DataFixtures\AppFixtures;
use App\Entity\Book;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookGetTest extends WebTestCase
{

    protected static $entityManager;
    private static $client;


    protected function tearDown(): void
    {
        parent::tearDown();

        $entityManager = self::$client->getContainer()->get('doctrine.orm.entity_manager');
        $connection = $entityManager->getConnection();
        $platform = $connection->getDatabasePlatform();
        $connection->executeUpdate($platform->getTruncateTableSQL('book', true /* whether to cascade */));
    }

    public static function setUpBeforeClass(): void
    {
        self::$client = static::createClient();
        self::bootKernel();
        self::$entityManager = self::getContainer()->get('doctrine')->getManager();
        $fixtures = new AppFixtures();
        $fixtures->load(self::$entityManager);
    }


    /**
     * @afterClass
     */
    public function testGetBook()
    {
        self::$client->request('GET', '/getBook?field=name&date=2023-03-01');

        $response = self::$client->getResponse();
        $this->assertEquals(
            '[{"name":"loic"}]',
            $response->getContent(),
            'La réponse JSON ne correspond pas à ce qui était attendu.'
        );
    }

}