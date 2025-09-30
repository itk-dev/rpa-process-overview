<?php

namespace App;

use App\Entity\ProcessOverview;
use League\Uri\Modifier;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Yaml;

class ProcessOverviewHelper
{
    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly DataSourceHelper $dataSourceHelper,
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
            $data = $this->dataSourceHelper->getProcessRun($datasource, $processId, $request->query->all());

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
                    array_map(fn (array $col) => [
                        'type' => 'text',
                        'value' => $this->getArrayValue($item, $col['data']),
                    ],
                        $metadataColumns),
                    array_map(static fn (array $step) => $step + ['type' => 'step'], $steps),
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
                    'data' => $data,
                ],
                'links' => $links,
                'meta' => $meta,
            ];
        } catch (\Exception $exception) {
            // @todo Log the exception
            throw $exception;
        }
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
