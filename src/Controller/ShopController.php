<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{

    public function __construct(
        private ApplicationRepository $applicationRepository
    )
    {
    }

    #[Route('/shop', name: 'app_shop')]
    public function index(): Response
    {
        return $this->render('hub/shop.html.twig', [
            'applications' => $this->applicationRepository->findAll()
        ]);
    }
}
