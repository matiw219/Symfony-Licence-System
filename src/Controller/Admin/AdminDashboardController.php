<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Entity\Genre;
use App\Entity\Licence;
use App\Entity\Release;
use App\Entity\User;
use App\Service\ApplicationService;
use App\Service\GenreService;
use App\Service\LicenceService;
use App\Service\ReleaseService;
use App\Service\UserService;
use App\Util\GraphUtil;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminDashboardController extends AbstractDashboardController
{

    public function __construct(
        private UserService $userService,
        private GenreService $genreService,
        private ApplicationService $applicationService,
        private ReleaseService $releaseService,
        private LicenceService $licenceService
    )
    {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/admin_dashboard.html.twig', [
            'userCount' => $this->userService->getUserCount(),
            'adminCount' => $this->userService->getAdminCount(),
            'genreCount' => $this->genreService->getGenreCount(),
            'applicationCount' => $this->applicationService->getApplicationCount(),
            'releaseCount' => $this->releaseService->getReleaseCount(),
            'licenceCount' => $this->licenceService->getLicenceCount(),
            'dates' => json_encode(array_reverse(GraphUtil::graphDates())),
            'applicationValues' => json_encode(array_values(GraphUtil::fixGraphData($this->applicationService->graphData()))),
            'releaseValues' => json_encode(array_values(GraphUtil::fixGraphData($this->releaseService->graphData()))),
            'licenceValues' => json_encode(array_values(GraphUtil::fixGraphData($this->licenceService->graphData()))),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Symfony Licence System');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('User', 'fa fa-user',User::class);
        yield MenuItem::linkToCrud('Genre', 'fa fa-list',Genre::class);
        yield MenuItem::linkToCrud('Application', 'fa fa-plug',Application::class);
        yield MenuItem::linkToCrud('Release', 'fa fa-file-shield',Release::class);
        yield MenuItem::linkToCrud('Licence', 'fa fa-key',Licence::class)
            ->setController(LicenceCrudController::class);
    }
}
