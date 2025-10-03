<?php

namespace App\Controller\Admin;

use App\Entity\DataSource;
use App\Entity\ProcessOverview;
use App\Entity\ProcessOverviewGroup;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

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
            ->setTitle($this->getParameter('site_title'));
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setMenuItems([
                // Remove the logout link.
            ]);
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud(t('Group'), null, ProcessOverviewGroup::class);
        yield MenuItem::linkToCrud(t('Process overview'), null, ProcessOverview::class);
        yield MenuItem::linkToCrud(t('Data source'), null, DataSource::class);

        yield MenuItem::section();
        yield MenuItem::linkToRoute(t('Home'), null, 'app_default');
    }
}
