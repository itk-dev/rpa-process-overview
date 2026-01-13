<?php

namespace App\Controller;

use App\Entity\ProcessOverviewGroup;
use App\Repository\ProcessOverviewGroupRepository;
use App\Security\Voter\PublishableEntityVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/group', name: 'process_overview_group_')]
final class ProcessOverviewGroupController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(ProcessOverviewGroupRepository $repository): Response
    {
        $groups = $repository->findPublished();

        return $this->render('process_overview_group/index.html.twig', [
            'groups' => $groups,
        ]);
    }

    #[Route('/{id}', name: 'show')]
    #[IsGranted(PublishableEntityVoter::VIEW, 'group')]
    public function show(ProcessOverviewGroup $group): Response
    {
        return $this->render('process_overview_group/show.html.twig', [
            'group' => $group,
        ]);
    }
}
