<?php

namespace App\Controller\Admin;

use App\Entity\ProcessOverview;
use App\Entity\ProcessOverviewGroup;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

#[AdminDashboard(routePath: '/admin', routeName: 'process_overview_admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(ProcessOverviewCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle(t('Process overview'));
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud(t('Group'), 'fas fa-list', ProcessOverviewGroup::class);
        yield MenuItem::linkToCrud(t('Process'), 'fas fa-list', ProcessOverview::class);
    }
}
