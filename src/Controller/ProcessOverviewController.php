<?php

namespace App\Controller;

use App\Entity\ProcessOverview;
use App\Entity\ProcessOverviewGroup;
use App\ProcessOverviewHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/group/{group}/overview', name: 'process_overview_')]
final class ProcessOverviewController extends AbstractController
{
    #[Route('/{overview}', name: 'show')]
    public function show(ProcessOverviewGroup $group, ProcessOverview $overview): Response
    {
        if ($group !== $overview->getGroup()) {
            throw new BadRequestHttpException();
        }

        return $this->render('process_overview/show.html.twig', [
            'overview' => $overview,
            'data_url' => $this->generateUrl('process_overview_data', [
                'group' => $group->getId(),
                'overview' => $overview->getId(),
            ]),
        ]);
    }

    #[Route('/{overview}/data', name: 'data')]
    public function data(ProcessOverview $overview, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->getData($overview);

        return new JsonResponse($data);
    }
}
