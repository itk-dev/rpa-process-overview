<?php

namespace App;

use App\Entity\ProcessOverview;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ProcessOverviewHelper
{
    public function __construct(
        private readonly PropertyAccessorInterface $propertyAccessor,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function getData(ProcessOverview $overview): array
    {
        try {
            $options = Yaml::parse($overview->getOptions());

            $url = $this->getArrayValue($options, 'data_source.url');

            $response = $this->httpClient->request(Request::METHOD_GET, $url);

            $data = $response->toArray();

            $metadataColumnsOptions = $this->getArrayValue($options, 'metadata_columns') ?? [];

            $metadataColumns = [];
            $stepColumns = [];
            foreach ($metadataColumnsOptions as $column) {
                $metadataColumns[] = $column + [
                    'type' => $column['type'] ?? 'text',
                ];
            }

            $rows = [];
            foreach ($data as $index => $item) {
                $steps = $item['steps'] ?? null;
                if (!$steps) {
                    break;
                }
                if (0 === $index) {
                    foreach ($steps as $stepIndex => $step) {
                        $stepColumns[] = [
                            'label' => $step['label'] ?? $step['name'] ?? $stepIndex,
                            'type' => 'step',
                        ];
                    }
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

            return [
                'rows' => $rows,
                'columns' => array_merge($metadataColumns, $stepColumns),
                'data' => $data,
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
}
