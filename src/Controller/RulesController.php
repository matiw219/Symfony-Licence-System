<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RulesController extends AbstractController
{

    #[Route('/rules', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('hub/rules.html.twig');
    }

}