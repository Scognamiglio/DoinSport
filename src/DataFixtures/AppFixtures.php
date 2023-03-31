<?php

namespace App\DataFixtures;


use App\Entity\Book;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $book = new Book();
        $book
            ->setState(1)
            ->setDate(new \DateTime('2023-03-01'))
            ->setNbrPerson(3)
            ->setTime(4)
            ->setActivity('golf')
            ->setPhone('0652858045')
            ->setEmail('loic.scognamiglio16@gmail.com')
            ->setName('loic')
            ->setLastname('Scognamiglio');
        $manager->persist($book);
        $manager->flush();
    }
}
