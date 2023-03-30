<?php

namespace App\Controller;
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
     */
    public function bookConfirm(int $id): Response
    {
        return $this->render('Book/bookConfirm.html.twig',
            [
                'name' => 'Loïc',
                'lastName' => 'Scognamiglio',
                'date' => '2023-03-30',
                'id' => '40'
            ]);
    }


    /**
     * @Route("/book/{id}", name="patchBook")
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
}