<?php

namespace App;

use App\Entity\DataSource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DataSourceHelper
{
    private const string DEFAULT_API_BASE_PATH = 'api/v1/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function getProcesses(DataSource $dataSource): array
    {
        return $this->get($dataSource, 'process');
    }

    public function getProcess(DataSource $dataSource, string $processId): array
    {
        return $this->get($dataSource, 'process/'.$processId);
    }

    public function getProcessRun(DataSource $dataSource, string $processId, array $query): array
    {
        return $this->get($dataSource, 'process/'.$processId.'/run', $query);
    }

    private function get(DataSource $dataSource, string $path, array $query = []): array
    {
        $url = $this->buildUrl($dataSource, $path, $query);
        $options = $this->buildOptions($dataSource);
        $response = $this->httpClient->request(Request::METHOD_GET, $url, $options);

        return $response->toArray();
    }

    private function buildUrl(DataSource $dataSource, string $path, array $query): string
    {
        $url = $dataSource->getUrl();

        $path = ltrim($path, '/');
        if (!str_starts_with($path, 'api/')) {
            $path = self::DEFAULT_API_BASE_PATH.$path;
        }

        $url = rtrim($url, '/').'/'.$path;

        if (!empty($query)) {
            $url .= (str_contains($url, '?') ? '&' : '?').$this->buildQueryString($query);
        }

        return $url;
    }

    private function buildOptions(DataSource $dataSource): array
    {
        $options = [];

        $dataSourceOptions = $this->getOptions($dataSource);
        if ($header = ($dataSourceOptions['auth']['header'] ?? null)) {
            foreach ($header as $name => $value) {
                $options['headers'][$name] = $value;
            }
        }

        return $options;
    }

    private function getOptions(DataSource $dataSource): array
    {
        try {
            $data = Yaml::parse($dataSource->getOptions() ?? '');
            if (is_array($data)) {
                return $data;
            }
        } catch (\Exception) {
        }

        return [];
    }

    /**
     * Build query string with proper handling of list values.
     */
    private function buildQueryString(array $params): string
    {
        // @see https://stackoverflow.com/a/8171667
        $query = http_build_query($params);

        return preg_replace('/%5B\d+%5D(?==)/', '', $query);
    }
}
