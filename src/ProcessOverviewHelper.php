<?php

namespace App;

use App\Entity\ProcessOverview;
use League\Uri\Modifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Yaml\Yaml;

class ProcessOverviewHelper
{
    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly DataSourceHelper $dataSourceHelper,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getData(Request $request, ProcessOverview $overview): array
    {
        try {
            $datasource = $overview->getDataSource();
            $processId = $overview->getProcessId();
            if (empty($datasource) || empty($processId)) {
                return [];
            }

            $options = $this->getOptions($overview);

            $process = $this->dataSourceHelper->getProcess($datasource, $processId);
            $query = $options['data']['default_query'] ?? null;
            if (!is_array($query)) {
                $query = [];
            }
            $query += $request->query->all();
            $data = $this->dataSourceHelper->getProcessRuns($datasource, $processId, $query);

            $metadataColumns = [];
            $metadataColumnsOptions = $this->getArrayValue($options, 'metadata_columns') ?? [];
            foreach ($metadataColumnsOptions as $column) {
                $metadataColumns[] = $column + [
                    'type' => $column['type'] ?? 'text',
                ];
            }

            // Add step columns
            $stepColumns = [];
            foreach ($process['steps'] as $step) {
                $stepColumns[] = [
                    'label' => $step['name'],
                    'type' => 'step',
                ];
            }

            $rows = [];
            $items = $data['items'] ?? [];
            foreach ($items as $item) {
                $steps = $item['steps'] ?? null;
                if (!$steps) {
                    break;
                }
                $rows[] = array_merge(
                    array_map(
                        function (array $col) use ($overview, $item) {
                            $value = $this->getArrayValue($item, $col['data']);
                            $result = [
                                'type' => 'text',
                            ];

                            if (isset($col['mask']['search'], $col['mask']['replace'])) {
                                $value = @preg_replace($col['mask']['search'], $col['mask']['replace'], $value);

                                $result['raw_value_url'] = $this->urlGenerator->generate('process_overview_raw_data_field',
                                    [
                                        'group' => $overview->getGroup()->getId(),
                                        'overview' => $overview->getId(),
                                        'run' => $item['id'],
                                        'field' => $col['data'],
                                    ], UrlGeneratorInterface::ABSOLUTE_URL);
                            }
                            $result['value'] = $value;

                            return $result;
                        },
                        $metadataColumns
                    ),
                    array_map(
                        function (array $step) use ($overview) {
                            return $step + [
                                'type' => 'step',
                                'rerun_url' => $step['can_rerun'] ? $this->urlGenerator->generate('process_overview_rerun',
                                    [
                                        'group' => $overview->getGroup()->getId(),
                                        'overview' => $overview->getId(),
                                        'run' => $step['id'],
                                    ], UrlGeneratorInterface::ABSOLUTE_URL) : null,
                            ];
                        },
                        $steps
                    ),
                );
            }

            $modifier = Modifier::from($request->getUri());
            $page = $data['page'] ?? 1;
            $links = [
                'self' => $modifier->getUriString(),
            ];
            if ($page > 1) {
                $links['prev'] = $modifier->mergeQueryParameters(['page' => $page - 1])->getUriString();
            }
            if ($page < ($data['pages'] ?? 0)) {
                $links['next'] = $modifier->mergeQueryParameters(['page' => $page + 1])->getUriString();
            }
            $meta = array_filter([
                'total' => $data['total'] ?? null,
            ]);

            return [
                'data' => [
                    'columns' => array_merge($metadataColumns, $stepColumns),
                    'rows' => $rows,
                ],
                'links' => $links,
                'meta' => $meta,
            ];
        } catch (\Exception $exception) {
            // @todo Log the exception
            throw $exception;
        }
    }

    public function getRawRunFieldValue(Request $request, ProcessOverview $overview, string $run, string $field)
    {
        $datasource = $overview->getDataSource();
        $processId = $overview->getProcessId();

        if (empty($datasource) || empty($processId)) {
            return [];
        }

        $data = $this->dataSourceHelper->getProcessRun($datasource, $processId, $run);

        return [
            'field' => $field,
            'value' => $this->getArrayValue($data, $field),
        ];
    }

    public function rerun(Request $request, ProcessOverview $overview, string $run)
    {
        $datasource = $overview->getDataSource();
        $processId = $overview->getProcessId();
        if (empty($datasource) || empty($processId)) {
            return [];
        }

        return $this->dataSourceHelper->rerun($datasource, $run);
    }

    private function getArrayValue(array $array, string $key): mixed
    {
        $propertyPath = '['.str_replace('.', '][', $key).']';

        return $this->propertyAccessor->getValue($array, $propertyPath);
    }

    private function getOptions(ProcessOverview $overview): array
    {
        try {
            $data = Yaml::parse($overview->getOptions() ?? '');
            if (is_array($data)) {
                return $data;
            }
        } catch (\Exception) {
        }

        return [];
    }
}
