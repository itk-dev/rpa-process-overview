<?php

namespace App\Controller;

use App\Entity\ProcessOverview;
use App\Entity\ProcessOverviewGroup;
use App\ProcessOverviewHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Yaml\Yaml;

use function Symfony\Component\Translation\t;

#[Route('/group/{group}/overview', name: 'process_overview_')]
final class ProcessOverviewController extends AbstractController
{
    #[Route('/{overview}', name: 'show')]
    public function show(ProcessOverviewGroup $group, ProcessOverview $overview): Response
    {
        if ($group !== $overview->getGroup()) {
            throw new BadRequestHttpException();
        }

        $overviewData = Yaml::parse($overview->getOptions() ?? '');

        return $this->render('process_overview/show.html.twig', [
            'overview' => $overview,
            'overview_config' => [
                'data_url' => $this->generateUrl('process_overview_data', [
                    'group' => $group->getId(),
                    'overview' => $overview->getId(),
                ]),
                'messages' => [
                    'Go to previous page' => strval(t('Go to previous page')),
                    'Go to page' => strval(t('Go to page')),
                    'Go to next page' => strval(t('Go to next page')),
                    'Missing data' => strval(t('Missing data')),
                    'Failed processes' => strval(t('Failed processes')),
                    'Loading data...' => strval(t('Loading data...')),
                    'of' => strval(t('of')),
                    'An error occurred while fetching the data' => strval(t('An error occurred while fetching the data')),
                ],
                'page_size' => $overviewData['data']['page_size'] ?? 5,
            ],
            'search_config' => [
                'search_url' => $this->generateUrl('process_overview_search', [
                    'group' => $group->getId(),
                    'overview' => $overview->getId(),
                ]),
                'characters_before_search' => $overviewData['search']['characters_before_search'] ?? 2,
                'messages' => [
                    'Citizen search' => strval(t('Citizen search')),
                    'Citizen information' => strval(t('Citizen information')),
                    'An error occurred while searching' => strval(t('An error occurred while searching')),
                ],
            ],
        ]);
    }

    #[Route('/{overview}/data', name: 'data')]
    public function data(Request $request, ProcessOverview $overview, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->getData($request, $overview);

        return new JsonResponse($data);
    }

    #[Route('/{overview}/search', name: 'search')]
    public function search(Request $request, ProcessOverview $overview, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->getData($request, $overview);

        return new JsonResponse($data);
    }
}
