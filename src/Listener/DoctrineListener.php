<?php

namespace App\Listener;

use App\Entity\Application;
use App\Service\FileService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Psr\Log\LoggerInterface;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
class DoctrineListener
{

    public function __construct(
        private FileService $fileService,
        private LoggerInterface $logger
    )
    {}

    public function postPersist(PostPersistEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof Application) {
            $this->fileService->createFolder($object->getName());
            $this->logger->info('Folder created!');
        }
    }
}