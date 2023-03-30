<?php

namespace App\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\BookService;

class ShowMailController extends AbstractController
{

    /**
     * @Route("/showMail", name="showMail")
     */
    public function index(): Response
    {

        return $this->render('Emails/sendBook.html.twig', [
            'name' => 'Loïc',
            'lastName' => 'Scognamiglio',
            'date' => '2023-03-30',
            'id' => '40'
        ]);
    }

}