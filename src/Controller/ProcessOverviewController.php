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

        $overviewOptions = Yaml::parse($overview->getOptions() ?? '');

        return $this->render('process_overview/show.html.twig', [
            'overview' => $overview,
            'overview_config' => [
                'data_url' => $this->generateUrl('process_overview_data', [
                    'group' => $group->getId(),
                    'overview' => $overview->getId(),
                ]),
                'messages' => array_map('strval', [
                    'Go to previous page' => strval(t('Go to previous page')),
                    'Go to page' => t('Go to page'),
                    'Go to next page' => t('Go to next page'),
                    'Missing data' => t('Missing data'),
                    'Failed processes' => t('Failed processes'),
                    'An error occurred when the process was restarted' => t('An error occurred when the process was restarted'),
                    'Rerun step' => t('Rerun step'),
                    'Finished at' => t('Finished at'),
                    'Error code' => t('Error code'),
                    'Loading data...' => t('Loading data...'),
                    'of' => t('of'),
                    'An error occurred while fetching the data' => t('An error occurred while fetching the data'),
                ]),
                'page_size' => $overviewOptions['data']['page_size'] ?? 5,
            ],
            'search_config' => [
                'search_url' => $this->generateUrl('process_overview_search', [
                    'group' => $group->getId(),
                    'overview' => $overview->getId(),
                ]),
                'minimum_search_query_length' => $overviewOptions['search']['minimum_search_query_length'] ?? 2,
                'messages' => array_map('strval', [
                    'Citizen search' => t('Citizen search'),
                    'Citizen information' => t('Citizen information'),
                    'An error occurred while searching' => t('An error occurred while searching'),
                ]),
            ],
        ]);
    }

    #[Route('/{overview}/data', name: 'data')]
    public function data(Request $request, ProcessOverview $overview, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->getData($request, $overview);

        return new JsonResponse($data);
    }

    #[Route('/{overview}/data/{run}/raw-data/{field}', name: 'raw_data_field')]
    public function getRawRunFieldValue(Request $request, ProcessOverview $overview, string $run, string $field, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->getRawRunFieldValue($request, $overview, $run, $field);

        return new JsonResponse($data);
    }

    #[Route('/{overview}/runs/{run}/rerun', name: 'rerun', methods: [Request::METHOD_POST])]
    public function rerun(Request $request, ProcessOverview $overview, string $run, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->rerun($request, $overview, $run);

        return new JsonResponse($data);
    }

    #[Route('/{overview}/search', name: 'search')]
    public function search(Request $request, ProcessOverview $overview, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->getData($request, $overview);

        return new JsonResponse($data);
    }
}
