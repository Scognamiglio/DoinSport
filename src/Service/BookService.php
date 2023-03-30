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

        // Dans un cas de figure concret, créer un service gérant la limite de réservation, que ça soit par salle, participant ou horaire. Le tout renseigné dans la configuration client
        $limit = $this->nbrLimitReservation('monClient');
        $myDate = new DateTime($fct('date'));

        $nbrAlready = $this
            ->bookRepository
            ->count(['date'=>$myDate]);


        if($nbrAlready >= $limit){
            return false; // Il serait plus propre de renvoyer une exception et de la gérer avec throw new
        }

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

        return true;
    }

    /**
     * @param string $client
     */
    public function nbrLimitReservation(String $client) : int
    {
        return 2;
    }
}