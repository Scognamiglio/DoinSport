<?php

namespace App\Controller;
use App\Entity\Book;
use App\Exception\NoBookException;
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
     * @Route("/bookConfirm/{id}", name="bookConfirm")
     * @param int $id
     * @return Response
     */
    public function bookConfirm(int $id): Response
    {
        $data = $this->bookService->searchBook(['name','lastname','date','id'],['id'=>$id]);
        var_dump($data);
        if(empty($data)){
            throw new NoBookException("Aucune réservation pour l'id $id");
        }

        return $this->render('Book/bookConfirm.html.twig',$data[0]);
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