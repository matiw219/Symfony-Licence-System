<?php

namespace App\Controller\Hub;

use App\Entity\Licence;
use App\Entity\Release;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HubController extends AbstractDashboardController
{
    #[Route('/hub', name: 'hub')]
    public function index(): Response
    {
        return $this->render('admin/hub_dashboard.html.twig', []);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Symfony Licence System');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Licence', 'fa fa-key',Licence::class)
        ->setController(UserLicenceCrudController::class);
        yield MenuItem::linkToCrud('Release', 'fa fa-file-shield',Release::class)
        ->setController(UserReleaseCrudController::class);
    }
}
