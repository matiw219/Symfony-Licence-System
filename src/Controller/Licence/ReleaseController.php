<?php

namespace App\Controller\Licence;

use App\Repository\ReleaseRepository;
use App\Service\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReleaseController extends AbstractController
{

    public function __construct(
        private ReleaseRepository $releaseRepository,
        private FileService $fileService
    )
    {}

    #[Route('/release/download/{id}', name: 'app_release_download')]
    public function downloadRelease(int $id) : Response {
        $release = $this->releaseRepository->find($id);

        if (!$release) {
            return new JsonResponse('Not found this release.');
        }

        $filePath = $this->fileService->getRelease($release);

        if (!file_exists($filePath)) {
            return new JsonResponse('Not found file for this release.');
        }

        FileService::downloadFile($filePath, pathinfo($filePath)['basename']);
        return new JsonResponse();

    }
}