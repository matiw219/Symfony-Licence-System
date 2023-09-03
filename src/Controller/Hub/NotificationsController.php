<?php

namespace App\Controller\Hub;

use App\Entity\User;
use App\Repository\ApplicationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController
{

    #[Route('/notifications', name: 'app_notifications')]
    public function index(): Response
    {
        return $this->render('hub/notifications.html.twig');
    }
}
