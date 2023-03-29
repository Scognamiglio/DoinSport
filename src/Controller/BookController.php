<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
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

        return new Response(json_encode($ret, true)); // Créer un objet Response et le renvoyer


    }
}