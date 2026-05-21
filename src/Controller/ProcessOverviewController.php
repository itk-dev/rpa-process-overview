<?php

namespace App\Controller;

use App\Entity\ProcessOverview;
use App\Entity\ProcessOverviewGroup;
use App\ProcessOverviewHelper;
use App\Security\Voter\PublishableEntityVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function Symfony\Component\Translation\t;

#[Route('/group/{group}/overview', name: 'process_overview_')]
final class ProcessOverviewController extends AbstractController
{
    #[Route('/{overview}', name: 'show')]
    #[IsGranted(PublishableEntityVoter::VIEW, 'overview')]
    public function show(ProcessOverviewGroup $group, ProcessOverview $overview, TranslatorInterface $translator): Response
    {
        if ($group !== $overview->getGroup()) {
            throw new BadRequestHttpException();
        }

        $overviewOptions = Yaml::parse($overview->getOptions() ?? '');
        $translate = static fn (TranslatableInterface $t): string => $t->trans($translator);

        return $this->render('process_overview/show.html.twig', [
            'overview' => $overview,
            'overview_config' => [
                'data_url' => $this->generateUrl('process_overview_data', [
                    'group' => $group->getId(),
                    'overview' => $overview->getId(),
                ]),
                'messages' => array_map($translate(...), [
                    'Go to previous page' => t('Go to previous page'),
                    'Go to page {page}' => t('Go to page {page}'),
                    'Go to next page' => t('Go to next page'),
                    'Missing data' => t('Missing data'),
                    'Try again!' => t('Error copying value. Try again!'),
                    'Copied!' => t('Value copied!'),
                    'Copy field' => t('Copy value'),
                    // We're actually showing “process runs”, but it probably makes more sense to real people to (still) call them “processes”.
                    'Showing processes failed in' => t('Showing processes failed in'),
                    'Show all' => t('Show all'),
                    'The process was restarted' => t('The process was restarted'),
                    'An error occurred when trying to restart the process' => t('An error occurred when trying to restart the process'),
                    'Rerun step' => t('Rerun step'),
                    'Finished at {finishedAt}' => t('Finished at {finishedAt}'),
                    'Failed {failedAt} at' => t('Failed at {failedAt}'),
                    'Error code: {code}' => t('Error code: {code}'),
                    'Loading data …' => t('Loading data …'),
                    'Showing {from}–{to} of {total}' => t('Showing {from}–{to} of {total}'),
                    'An error occurred while fetching the data' => t('An error occurred while fetching the data'),
                    'Remove filter on "{value}"' => t('Remove filter on "{value}"'),
                    'Show only "{value}"' => t('Show only "{value}"'),
                ]),
                'page_size' => $overviewOptions['data']['page_size'] ?? 5,
                'title' => $overviewOptions['data']['title'] ?? '',
            ],
            'search_config' => [
                'search_url' => $this->generateUrl('process_overview_search', [
                    'group' => $group->getId(),
                    'overview' => $overview->getId(),
                ]),
                'process_id' => $overview->getId(),
                'minimum_search_query_length' => $overviewOptions['search']['minimum_search_query_length'] ?? 2,
                'messages' => array_map($translate(...), [
                    'Citizen search' => t('Citizen search'),
                    'Citizen information' => t('Citizen information'),
                    'An error occurred while searching' => t('An error occurred while searching'),
                ]),
                'title' => $overviewOptions['search']['title'] ?? '',
            ],
        ]);
    }

    #[Route('/{overview}/data', name: 'data')]
    #[IsGranted(PublishableEntityVoter::VIEW, 'overview')]
    public function data(Request $request, ProcessOverview $overview, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->getData($request, $overview);

        return new JsonResponse($data);
    }

    #[Route('/{overview}/data/{run}/raw-data/{field}', name: 'raw_data_field')]
    #[IsGranted(PublishableEntityVoter::VIEW, 'overview')]
    public function getRawRunFieldValue(Request $request, ProcessOverview $overview, string $run, string $field, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->getRawRunFieldValue($request, $overview, $run, $field);

        return new JsonResponse($data);
    }

    #[Route('/{overview}/runs/{run}/rerun', name: 'rerun', methods: [Request::METHOD_POST])]
    #[IsGranted(PublishableEntityVoter::VIEW, 'overview')]
    public function rerun(Request $request, ProcessOverview $overview, string $run, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->rerun($request, $overview, $run);

        return new JsonResponse($data);
    }

    #[Route('/{overview}/search', name: 'search')]
    #[IsGranted(PublishableEntityVoter::VIEW, 'overview')]
    public function search(Request $request, ProcessOverview $overview, ProcessOverviewHelper $helper): Response
    {
        $data = $helper->search($request, $overview);

        return new JsonResponse($data);
    }
}
