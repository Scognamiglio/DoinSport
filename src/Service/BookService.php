<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;


class BookService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __construct(
        private BookRepository $bookRepository
    )
    {
        $this->bookRepository = $bookRepository;
    }

    /**
     * @param request $request
     */
    public function saveBook(Request $request) : bool
    {
        // Pas ultra pertinent, mais j'aime bien les lambda. Si un traitement identique plusieurs fois => créer un transformer pour passé du format $request à $entity
        $fct = function ($field) use ($request) {return $request->request->get($field);};
        $book = new Book();
        $book
            ->setName($fct('prenom'))
            ->setLastname($fct('nom'))
            ->setEmail($fct('mail'))
            ->setPhone($fct('phone'))
            ->setActivity($fct('sport'))
            ->setTime($fct('time'))
            ->setNbrPerson($fct('nbr'))
            ->setDate(new DateTime($fct('date')))
            ->setState(0); // 0 pas de mail envoyé, mais enregistrer

        $this->bookRepository->save($book,true);

        return $request->request->get('nom') == 'blbl2';
    }
}