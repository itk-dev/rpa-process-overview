<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class ProxyApiController extends AbstractController
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly LoggerInterface $logger,
        private array $options,
    ) {
    }

    #[Route('/proxy/{path}', name: 'app_proxy_api', requirements: ['path' => '.*'])]
    public function index(Request $request, string $path): Response
    {
        $method = $request->getMethod();
        $url = rtrim($this->options['api_base_url'], '/').'/'.$path;
        $options = [
            'headers' => $request->headers->all(),
            'query' => $request->query->all(),
            'body' => $request->getContent(),
        ];
        try {
            $response = $this->httpClient->request($method, $url, $options);

            return new Response(
                $response->getContent(),
                $response->getStatusCode(),
                $response->getHeaders()
            );
        } catch (\Exception $e) {
            $this->logger->error(sprintf('%s: %s', $e::class, $e->getMessage()));
            if ($e instanceof ClientExceptionInterface) {
                $response = $e->getResponse();

                return new Response(
                    $response->getContent(),
                    $response->getStatusCode(),
                    $response->getHeaders()
                );
            }
            throw new BadRequestHttpException();
        }
    }
}
