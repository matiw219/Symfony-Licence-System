<?php

namespace App\Controller;

use App\Repository\ApplicationRepository;
use App\Repository\LicenceRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CheckLicenceController extends AbstractController
{

    public function __construct(
        private ApplicationRepository $applicationRepository,
        private UserRepository $userRepository,
        private LicenceRepository $licenceRepository
    )
    {}


    #[Route('/check', name: 'check_licence')]
    public function check(Request $request) : Response
    {
        $app = $request->headers->get('App', null);
        $username = $request->headers->get('User', null);
        $key = $request->headers->get('Key', null);
        $port = $request->headers->get('Port', null);
        $ip = $request->getClientIp();

        if (!$app || !$username || !$key) {
            return new JsonResponse('Not authenticated', 401);
        }

        $user = $this->userRepository->findOneBy(['username' => $username]);
        if (!$user) {
            $user = $this->userRepository->findOneBy(['email' => $username]);
        }
        if (!$user) {
            return new JsonResponse('Not authenticated #2', 401);
        }

        $application = $this->applicationRepository->findOneBy(['name' => $app]);
        if (!$application) {
            $application = $this->applicationRepository->findOneBy(['id' => $app]);
        }
        if (!$application) {
            return new JsonResponse('Not authenticated #3', 401);
        }

        $licence = $this->licenceRepository->findOneBy(['user' => $user, 'licenceKey' => $key]);

        if ($licence) {
            if ($licence->isRequireHost()) {
                if ($licence->getHost() != $ip) {
                    return new JsonResponse('You try to use this app on another remote server');
                }

                if ($licence->isRequirePort()) {
                    if ($licence->getPort() != $port) {
                        return new JsonResponse('You try to use this app on correct server but incorrect port');
                    }
                }
            }
            if ($licence->getApplication() !== $application) {
                return new JsonResponse('This licence key is not for this application');
            }
            return new JsonResponse(true);
        }
        return new JsonResponse(false);
    }

}