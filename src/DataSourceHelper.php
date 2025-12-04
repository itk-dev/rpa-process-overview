<?php

namespace App;

use App\Entity\DataSource;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use function Symfony\Component\String\u;

class DataSourceHelper
{
    private const string DEFAULT_API_BASE_PATH = 'api/v1/';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly Security $token,
    ) {
    }

    public function getProcesses(DataSource $dataSource): array
    {
        return $this->get($dataSource, 'processes/');
    }

    public function getProcess(DataSource $dataSource, string $processId): array
    {
        return $this->get($dataSource, 'processes/'.$processId);
    }

    public function getProcessRuns(DataSource $dataSource, string $processId, array $query): array
    {
        return $this->get($dataSource, 'runs/', ['process_id' => $processId] + $query);
    }

    public function search(DataSource $dataSource, array $query): array
    {
        return $this->get($dataSource, 'runs/search/', $query);
    }

    public function getProcessRun(DataSource $dataSource, string $processId, string $runId, ?string $action = null): array
    {
        return $this->get($dataSource, 'runs/'.$runId, action: $action);
    }

    public function rerun(DataSource $dataSource, string $runId): array
    {
        return $this->post($dataSource, 'step-runs/'.$runId.'/rerun');
    }

    private function get(DataSource $dataSource, string $path, array $query = [], ?string $action = null): array
    {
        $url = $this->buildUrl($dataSource, $path, $query);
        $options = $this->buildOptions($dataSource, action: $action ?? $this->getActionName());
        $response = $this->httpClient->request(Request::METHOD_GET, $url, $options);

        return $response->toArray();
    }

    private function post(DataSource $dataSource, string $path, array $query = [], ?string $action = null): array
    {
        $url = $this->buildUrl($dataSource, $path, $query);
        $options = $this->buildOptions($dataSource, action: $action ?? $this->getActionName());
        $response = $this->httpClient->request(Request::METHOD_POST, $url, $options);

        return $response->toArray();
    }

    /**
     * Get action name based on current call stack.
     *
     * @param int $offset the call stack offset
     *
     * @return string|null the action name if any
     */
    private function getActionName(int $offset = 3): ?string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $offset);
        if ($offset !== count($trace)) {
            return null;
        }
        $frame = array_pop($trace);
        $function = $frame['function'] ?? null;
        if (null === $function) {
            return null;
        }

        return $function;
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

    private function buildOptions(DataSource $dataSource, ?string $action): array
    {
        $options = [];

        $dataSourceOptions = $this->getOptions($dataSource);
        if ($clientOptions = ($dataSourceOptions['client_options'] ?? null)) {
            $options += $clientOptions;
        }

        $options['headers']['x-user'] = $this->token->getUser()?->getUserIdentifier();
        if (null !== $action) {
            $options['headers']['x-action'] = u($action)->kebab()->toString();
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
