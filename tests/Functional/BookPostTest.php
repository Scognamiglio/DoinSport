<?php
namespace App\Tests\Functional;

use App\DataFixtures\AppFixtures;
use App\Entity\Book;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class BookPostTest extends WebTestCase
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
    public function testPostBook(){

        self::$client->request('POST', '/postBook', [
            'nom' => 'nom',
            'prenom' => 'prenom',
            'mail' => 'loic.scognamiglio16@gmail.com',
            'phone' => '0652858045',
            'sport' => 'tennis',
            'time' => '2',
            'nbr' => '2',
            'date' => '2023-03-11',
        ]);


        // Vérifie que la réponse contient le code de statut HTTP 201
        $this->assertEquals(200, self::$client->getResponse()->getStatusCode());

        // Vérifie que l'entité a bien été enregistrée en base de données
        $entityManager = self::$client->getContainer()->get('doctrine.orm.entity_manager');
        $bookRepository = $entityManager->getRepository(Book::class);
        $book = $bookRepository->findOneBy(['date' => new DateTime('2023-03-11')]);
        $this->assertInstanceOf(Book::class, $book);
        $this->assertEquals('0652858045', $book->getPhone());
    }

}