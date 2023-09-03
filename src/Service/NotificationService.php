<?php

namespace App\Service;

use App\Entity\Notification;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{

    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager
    )
    {
    }

    public function sendAll(string $message) : void {
        foreach ($this->userRepository->findAll() as $user) {
            $notifiaction = new Notification();
            $notifiaction->setUser($user);
            $notifiaction->setSender(null);
            $notifiaction->setSendAt(new \DateTimeImmutable());
            $notifiaction->setMessage($message);
            $this->entityManager->persist($notifiaction);
        }

        $this->entityManager->flush();
    }

}