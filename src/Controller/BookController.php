<?php

namespace App\Controller;
use App\Entity\Book;
use App\Exception\NoBookException;
use App\Enum\State;
use Doctrine\Common\Annotations\Annotation\Enum;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BookService;

class BookController extends AbstractController
{

    public function __construct(
        private BookService $bookService
    )
    {
        $this->bookService = $bookService;
    }
    /**
     * @Route("/book", name="book")
     * @return Response
     */
    public function index(): Response
    {
        $ConfClient = ['tennis','padel','squash']; // Dans un gros projet, gérer cette data à partir de la configuration du client pour la rendre dynamique et propre au client
        return $this->render('Book/index.html.twig', [
            'optionsActivity' => $ConfClient
        ]);
    }

    /**
     * @Route("/bookConfirm/{id}", name="bookConfirm")
     * @param int $id
     * @return Response
     */
    public function bookConfirm(int $id): Response
    {
        $data = $this->bookService->searchBook(['name','lastname','date','id'],['id'=>$id]);
        if(empty($data)){
            throw new NoBookException("Aucune réservation pour l'id $id");
        }

        return $this->render('Book/bookConfirm.html.twig',$data[0]);
    }

    /**
     * @Route("/showBook", name="showBook")
     * @param Request $request
     * @return Response
     */
    public function showBook(Request $request): Response
    {
        $order = explode(" ", $request->query->get('order') ?? 'date asc');

        $fieldToUse = ['id','lastname','name','email','phone','activity','time','nbrPerson','date','state'];
        $where = array_filter($request->query->all(),function ($f) use ($fieldToUse){
           return in_array($f,$fieldToUse);
        },ARRAY_FILTER_USE_KEY );

        $data = $this
            ->bookService
            ->searchBook($fieldToUse,$where,[$order[0]=>$order[1]]);

        $allParam = array_merge([
            'allData'=>$data,
                'fieldToUse' => $fieldToUse,
                'orderField' => $order[0],
                'orderArrow' => $order[1]
            ],$where);
        return $this->render('Book/showBook.html.twig',$allParam);
    }


    public function getState(string $key) : string
    {
        return State::search($key);
    }








    /**
     * @Route("/postBook", name="postBook")
     * @param Request $request
     * @return Response
     */
    public function postBook(Request $request): Response
    {
        $ret = $this
            ->bookService
            ->saveBook($request);

        return new Response(json_encode($ret, true));
    }


    /**
     * @Route("/book/{id}", name="patchBook")
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function patchBook(Request $request,int $id): Response
    {
        $data = json_decode($request->getContent(), true);
        try{
            $this
                ->bookService
                ->editBook($id,[$data['field']=>$data['value']]);
            return new Response(json_encode(true, true));
        }catch (\Exception $e){
            $classErrorName = get_class($e);
            throw new $classErrorName($e->getMessage());
        }
    }

    /**
     * @Route("/getBook", name="getBook")
     * @param Request $request
     * @return Response
     */
    public function getBook(Request $request): Response
    {
        try{
            $listBook = $this
                ->bookService
                ->getBook($request);
            return new Response(json_encode($listBook, true));
        }catch (\Exception $e){
            $classErrorName = get_class($e);
            throw new $classErrorName($e->getMessage());
        }
    }

}