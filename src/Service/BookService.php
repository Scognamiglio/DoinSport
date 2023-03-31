<?php

namespace App\Service;

use App\Entity\Book;
use App\Exception\NoBookException;
use App\Exception\NoFieldKnownException;
use App\Exception\OrderNotUsableException;
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
            ->htmlTemplate('Emails/sendBook.html.twig')
            ->context([
                'name' => $book->getName(),
                'lastname' => $book->getName(),
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

    /**
     * @param Request $request
     */
    public function getBook(Request $request) : array // Peut-être un peu trop longue, il serait intéressant de l'exploser en 3 petites méthodes
    {
        $diffFields = (new Book())->getAllFields();
        $fields = $request->query->get('field');
        $where = [];
        $order = [];


        if(!empty($fields)){
            $fields = explode(',',$fields);

            // Vérifie la confirmité des fields + mets en minuscule
            $fields = array_map(function ($field) use($diffFields)
            {
                if(!in_array(strtolower($field),$diffFields)){
                    throw new NoFieldKnownException("Le $field est non connu pour la réservation");
                }
                return strtolower($field);

            },$fields);
        }else{
            $fields = $diffFields;
        }

        foreach ($request->query->keys() as $key){
            if(in_array(strtolower($key),$diffFields)){
                $where[strtolower($key)] = $request->query->get($key);
            }
        }

        if(!empty($request->query->get('order'))){
            $tmp = explode(' ',$request->query->get('order'));

            $fieldOrder = strtolower($tmp[0]);
            if(!in_array($fieldOrder,$diffFields))
                throw new OrderNotUsableException("Le $fieldOrder est non connu pour order");

            if(count($tmp) == 1){
                $order[$fieldOrder] = 'asc';
            }else{
                $operator = strtolower($tmp[1]);
                if(!in_array($operator,['asc','desc']))
                    throw new OrderNotUsableException("L'operateur $operator est non connu pour order (asc/desc)");

                $order[$fieldOrder] = $operator;
            }
        }

        return $this
            ->searchBook($fields,$where,$order);
    }

    public function searchBook(array $fields,array $where = [],array $order = []): array
    {
        $ret = [];

        if(isset($where['date'])){
            $where['date'] = new DateTime($where['date']);
        }
        $books = $this
            ->bookRepository
            ->findBy($where,$order);


        foreach($books as $book){
            $ret[] = array_combine(
                $fields,
                array_map(function ($f)use($book){
                    $nameMethod = "get".ucfirst($f);
                    $value = $book->$nameMethod();
                    return is_object($value) && get_class($value) == 'DateTime'
                        ? $value->format('Y-m-d')
                        : $value;

                },$fields)
            );
        }
        return $ret;
    }
}