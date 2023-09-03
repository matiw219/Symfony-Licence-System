<?php

namespace App\Listener;

use App\Controller\Admin\ApplicationCrudController;
use App\Controller\Admin\GenreCrudController;
use App\Controller\Admin\UserCrudController;
use App\Entity\Application;
use App\Entity\Genre;
use App\Entity\User;
use App\Service\NotificationService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class EasyAdminListener implements EventSubscriberInterface
{

    public function __construct(
        private LoggerInterface $logger,
        private AdminUrlGenerator $adminUrlGenerator,
        private SessionInterface $session,
        private NotificationService $notificationService
    )
    {}

    public static function getSubscribedEvents()
    {
        return [
            BeforeEntityDeletedEvent::class => ['beforeEntityDelete'],
            AfterEntityPersistedEvent::class => ['afterEntityPersisted']
        ];
    }

    public function afterEntityPersisted(AfterEntityPersistedEvent $event) {
        $entity = $event->getEntityInstance();

        if ($entity instanceof Application) {
            $this->notificationService->sendAll('New application on our service: <b>' . $entity->getName() . '</b> in the genre: <b>' . $entity->getGenre() . '</b>.
                <br>You can find new app in the shop page.');
        }
    }

    public function beforeEntityDelete(BeforeEntityDeletedEvent $event) {
        $entity = $event->getEntityInstance();

        if ($entity instanceof User) {
            $collections = [];
            if (!$entity->getGenres()->isEmpty()) {
                $list = [];
                foreach ($entity->getGenres() as $genre) {
                    $list[] = $genre->getName();
                }
                $collections['Genres'] = $list;
            }
            if (!$entity->getApplications()->isEmpty()) {
                $list = [];
                foreach ($entity->getApplications() as $application) {
                    $list[] = $application->getName();
                }
                $collections['Applications'] = $list;
            }
            if (!$entity->getReleases()->isEmpty()) {
                $list = [];
                foreach ($entity->getReleases() as $release) {
                    $list[] = $release->getApplication()->getName() . ':' . $release->getVersion();
                }
                $collections['Releases'] = $list;
            }
            if (!$entity->getLicences()->isEmpty()) {
                $list = [];
                foreach ($entity->getLicences() as $licence) {
                    $list[] = $licence->getUser()->getEmail() . ':' . $licence->getApplication()->getName();
                }
                $collections['Licences'] = $list;
            }

            if (!empty($collections)) {
                $url = $this->adminUrlGenerator->setController(UserCrudController::class)->setAction(Action::INDEX)->generateUrl();

                $finalMessage = 'You cannot delete this user because it has related entities.<br>';

                $genres = $collections['Genres'];
                $applications = $collections['Applications'];
                $releases = $collections['Releases'];
                $licences = $collections['Licences'];

                if (!empty($genres)){
                    $finalMessage .= $this->buildMessage('Genres', $genres);
                }

                if (!empty($applications)) {
                    $finalMessage .= $this->buildMessage('Applications', $applications);
                }

                if (!empty($releases)) {
                    $finalMessage .= $this->buildMessage('Releases', $releases);
                }

                if (!empty($licences)) {
                    $finalMessage .= $this->buildMessage('Licences', $licences);
                }

                $this->addFlash('error', $finalMessage);

                $event->setResponse(new RedirectResponse($url));
            }
        }
        else if ($entity instanceof Genre) {
            $collections = [];

            if (!$entity->getApplications()->isEmpty()) {
                $list = [];
                foreach ($entity->getApplications() as $application) {
                    $list[] = $application->getName();
                }
                $collections['Applications'] = $list;
            }

            if (!empty($collections)) {
                $url = $this->adminUrlGenerator->setController(GenreCrudController::class)->setAction(Action::INDEX)->generateUrl();

                $finalMessage = 'You cannot delete this genre because it has related entities.<br>';

                $applications = $collections['Applications'];

                if (!empty($applications)) {
                    $finalMessage .= $this->buildMessage('Applications', $applications);
                }

                $this->addFlash('error', $finalMessage);

                $event->setResponse(new RedirectResponse($url));
            }
        }
        else if ($entity instanceof Application) {
            $collections = [];

            if (!$entity->getReleases()->isEmpty()) {
                $list = [];
                foreach ($entity->getReleases() as $release) {
                    $list[] = $release->getApplication()->getName() . ':' . $release->getVersion();
                }
                $collections['Releases'] = $list;
            }
            if (!$entity->getLicences()->isEmpty()) {
                $list = [];
                foreach ($entity->getLicences() as $licence) {
                    $list[] = $licence->getUser()->getEmail() . ':' . $licence->getApplication()->getName();
                }
                $collections['Licences'] = $list;
            }

            if (!empty($collections)) {
                $url = $this->adminUrlGenerator->setController(ApplicationCrudController::class)->setAction(Action::INDEX)->generateUrl();

                $finalMessage = 'You cannot delete this application because it has related entities.<br>';

                $releases = $collections['Releases'];
                $licences = $collections['Licences'];

                if (!empty($releases)) {
                    $finalMessage .= $this->buildMessage('Releases', $releases);
                }
                if (!empty($licences)) {
                    $finalMessage .= $this->buildMessage('Licences', $licences);
                }

                $this->addFlash('error', $finalMessage);

                $event->setResponse(new RedirectResponse($url));
            }
        }
    }

    public function buildMessage(string $name, array $array) : string {
        $message = $name . ':<ul>';
        foreach ($array as $item) {
            $message .= '<li>' . $item . '</li>';
        }
        $message .= '</ul>';
        return $message;
    }

    public function addFlash(string $type, string $message)
    {
        $this->session->getFlashBag()->add($type, $message);
    }

}