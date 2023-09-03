<?php

namespace App\Controller\Hub;

use App\Entity\User;
use App\Repository\ApplicationRepository;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NotificationsController extends AbstractController
{

    public function __construct(
        private NotificationRepository $notificationRepository
    )
    {
    }

    #[Route('/notifications', name: 'app_notifications')]
    public function index(): Response
    {
        return $this->render('hub/notifications.html.twig',[
            'notifications' => $this->notificationRepository->findBy(['user' => $this->getUser()], ['sendAt' => 'DESC'])
        ]);
    }
}
