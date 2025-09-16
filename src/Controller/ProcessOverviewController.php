<?php

namespace App\Controller;

use App\Entity\ProcessOverview;
use App\ProcessOverviewHelper;
use App\Repository\ProcessOverviewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/process/overview', name: 'process_overview_')]
final class ProcessOverviewController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(ProcessOverviewRepository $repository): Response
    {
        return $this->render('process_overview/index.html.twig', [
            'controller_name' => 'ProcessOverviewController',
        ]);
    }

    #[Route('/{id}', name: 'show')]
    public function show(ProcessOverview $overview): Response
    {
        return $this->render('process_overview/show.html.twig', [
            'overview' => $overview,
            'data_url' => $this->generateUrl('process_overview_data', ['id' => $overview->getId()]),
        ]);
    }

    #[Route('/{id}/data', name: 'data')]
    public function data(ProcessOverview $overview, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->getData($overview);

        return new JsonResponse($data);
    }
}
