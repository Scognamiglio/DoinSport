<?php

namespace App\Service;

use App\Entity\Book;
use App\Exception\NoBookException;
use App\Exception\NoFieldKnownException;
use App\Repository\BookRepository;
use DateTime;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;


class BookService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function __construct(
        private BookRepository $bookRepository,
        private MailerInterface $mailer
    )
    {
        $this->bookRepository = $bookRepository;
        $this->mailer = $mailer;
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
            return false; // Il serait plus propre de renvoyer une exception et de la gérer avec throw new, mais n'étant pas vraiment une erreur je ne l'ai pas fais.
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

        $this->sendMail($book);

        return true;
    }

    /**
     * @param string $client
     */
    public function nbrLimitReservation(String $client) : int
    {
        return 2;
    }

    // Idéalement à mettre dans un microService indépendant et privé pour centralisé les mails.
    // ça permettrais un meilleur contrôle des différents mails et de changer de solution si nécéssaire sans chercher de partout
    public function sendMail(Book $book) :void
    {
        $email = (new TemplatedEmail())
            ->from('loic.scognamiglio16@gmail.com')
            ->to($book->getEmail())
            ->subject('Test Mailer with Twig Template')
            ->htmlTemplate('emails/sendBook.html.twig')
            ->context([
                'name' => $book->getName(),
                'lastName' => $book->getName(),
                'date' => $book->getDate()->format('Y-m-d'),
                'id' => $book->getId()
            ]);

        $this->mailer->send($email);
    }

    public function editBook(int $id,$fieldAndValue) :void
    {
        $book = $this
            ->bookRepository
            ->find(['id'=>$id]);

        if(empty($book)){
            throw new NoBookException('Aucune réservation connu pour cette id');
        }

        foreach ($fieldAndValue as $field=>$value){
            $nameSet = "set".ucfirst($field);
            if(!method_exists($book,$nameSet)){
                throw new NoFieldKnownException("Le $field est non connu pour la réservation");
            }
            $book->$nameSet($value);
        }

        $this
            ->bookRepository
            ->save($book,true);
    }
}