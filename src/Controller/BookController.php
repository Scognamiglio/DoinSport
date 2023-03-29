<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    /**
     * @Route("/book", name="book")
     */
    public function index(): Response
    {
        $ConfClient = ['tennis','padel','squash']; // Dans un gros projet, gérer cette date à partir de la configuration du client pour la rendre dynamique et propre au client
        return $this->render('Book/index.html.twig', [
            'optionsActivity' => $ConfClient
        ]);
    }

    /**
     * @Route("/postBook", name="postBook")
     */
    public function postBook(Request $request)
    {
        $name = $request->request->get('nom');

        return new Response(json_encode($name, true)); // Créer un objet Response et le renvoyer


    }
}