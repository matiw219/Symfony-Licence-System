<?php

namespace App\Controller\Admin;

use App\Entity\Application;
use App\Entity\Genre;
use App\Entity\Licence;
use App\Entity\Release;
use App\Entity\User;
use App\Service\GenreService;
use App\Service\UserService;
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
    )
    {}

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/admin_dashboard.html.twig', [
            'userCount' => $this->userService->getUserCount(),
            'adminCount' => $this->userService->getAdminCount(),
            'genreCount' => $this->genreService->getGenreCount()
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
        yield MenuItem::linkToCrud('Licence', 'fa fa-key',Licence::class);
    }
}
