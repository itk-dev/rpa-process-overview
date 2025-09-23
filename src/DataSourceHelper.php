<?php

namespace App;

use App\Entity\DataSource;
use Symfony\Component\HttpFoundation\Request;
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

    public function getProcessRun(DataSource $dataSource, string $processId): array
    {
        return $this->get($dataSource, 'process/'.$processId.'/run');
    }

    private function get(DataSource $dataSource, string $path): array
    {
        $url = $this->buildUrl($dataSource, $path);
        $options = [];
        $response = $this->httpClient->request(Request::METHOD_GET, $url, $options);

        return $response->toArray();
    }

    private function buildUrl(DataSource $dataSource, string $path): string
    {
        $url = $dataSource->getUrl();

        $path = ltrim($path, '/');
        if (!str_starts_with($path, 'api/')) {
            $path = self::DEFAULT_API_BASE_PATH.$path;
        }

        return rtrim($url, '/').'/'.$path;
    }
}
