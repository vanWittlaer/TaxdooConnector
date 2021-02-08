<?php

namespace VanWittlaerTaxdooConnector\Components;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\FutureResponse;
use GuzzleHttp\Message\Request;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Ring\Future\FutureInterface;
use Shopware\Components\Logger;
use VanWittlaerTaxdooConnector\Services\Configuration;

class TaxdooClient
{
    const DEFAULT_LIMIT = 100;
    const DEFAULT_RESET = 60;
    const SUCCESS = [200];
    const RECOVERABLE = [400, 401];

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $config;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var int
     */
    private $delay = 0.0;

    /**
     * @var
     */
    private $lastRequest = 0.0;

    /**
     * TaxdooClient constructor.
     * @param Configuration $configuration
     * @param Logger $logger
     * @throws Exception
     */
    public function __construct(Configuration $configuration, Logger $logger)
    {
        $this->configuration = $configuration;
        $this->logger = $logger;
    }

    /**
     * @param string $endpoint
     * @param null $query
     * @return mixed
     * @throws Exception
     */
    public function get(string $endpoint, $query = null)
    {
        $response = $this->send('GET', $endpoint, $query);

        return $response->json();
    }

    /**
     * @param string $endpoint
     * @param null $query
     * @return mixed
     * @throws Exception
     */
    public function delete(string $endpoint, $query = null)
    {
        $response = $this->send('DELETE', $endpoint, $query);

        return $response->json();
    }

    /**
     * @param string $endpoint
     * @param mixed $payload
     * @return mixed
     * @throws Exception
     */
    public function post(string $endpoint, $payload = null)
    {
        $response = $this->send('POST', $endpoint, null, $payload);

        return $response->json();
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param null $query
     * @param null $payload
     * @return FutureResponse|ResponseInterface|FutureInterface|null
     * @throws Exception
     */
    public function send(string $method, string $endpoint, $query = null, $payload = null)
    {
        $request = $this->createRequest($method, $endpoint, $query, $payload);

        $this->logger->debug('TaxdooClient::' . $method . ' - Request', [
            'host' => $request->getHost(),
            'path' => $request->getPath(),
            'config' => $request->getConfig()->getKeys(),
            'query' => $request->getQuery(),
            'headers' => $request->getHeaders(),
            'payload' => json_decode(json_encode($payload), true),
        ]);

        // throttling - pro-actively comply to Taxdoo's rate limits
        $remaining = $this->delay + $this->lastRequest - microtime(true);
        if ($remaining > 0) {
            $this->logger->debug('TaxdooClient::' . $request->getMethod() . ' - Throttling ' . $remaining . ' seconds');
            usleep((int)ceil(1000000.0 * $remaining));
        }
        $this->lastRequest = microtime(true);

        try {
            $response = $this->getClient()->send($request);
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        $this->handleExceptions($method, $endpoint, $payload, $response);

        $this->logger->debug('TaxdooClient::' . $request->getMethod() . ' - Response ' . $response->getStatusCode(), [
            'headers' => $response->getHeaders(),
            'body' => $response->json(),
        ]);

        $this->recalculateDelay($response);

        return $response;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param $payload
     * @param ResponseInterface $response
     * @throws Exception
     */
    private function handleExceptions(
        string $method,
        string $endpoint,
        $payload,
        ResponseInterface $response
    ) {
        $taxdooResponse = $response->json();

        if (in_array($response->getStatusCode(), self::SUCCESS)) {
            if ($taxdooResponse['status'] === 'success') {

                return;
            }
            $this->logger->error('TaxdooClient::' . $method . ' - API-Response "fail"',
                [
                    'endpoint' => $endpoint,
                    'payload' => json_decode(json_encode($payload), true),
                    'errors' => $taxdooResponse,
                ]);

            return;
        }

        if (in_array($response->getStatusCode(), self::RECOVERABLE)) {
            $this->logger->error('TaxdooClient::' . $method . ' - Response ' . $response->getStatusCode(),
                [
                    'endpoint' => $endpoint,
                    'payload' => json_decode(json_encode($payload), true),
                    'errors' => $taxdooResponse,
                ]);

            return;
        }

        $this->logger->error('TaxdooClient::' . $method . ' - Response ' . $response->getStatusCode(),
            [
                'headers' => $response->getHeaders(),
                'endpoint' => $endpoint,
                'payload' => json_decode(json_encode($payload), true),
            ]);

        throw new Exception(
            'TaxdooClient Exception - HTTP Status Code ' . $response->getStatusCode() . ', see log for details.'
        );
    }

    /**
     * @param ResponseInterface $response
     */
    private function recalculateDelay(ResponseInterface $response)
    {
        $headers = $response->getHeaders();

        $limit = $headers['X-RateLimit-Limit'] ? (float)array_shift($headers['X-RateLimit-Limit']) : self::DEFAULT_LIMIT;
        $reset = $headers['X-RateLimit-Reset'] ? (float)array_shift($headers['X-RateLimit-Reset']) : self::DEFAULT_RESET;
        $this->delay = $reset / $limit;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param $query
     * @param $payload
     * @return Request
     */
    private function createRequest(string $method, string $endpoint, $query, $payload): Request
    {

        return $this->getClient()->createRequest($method, $this->getUri($endpoint), $this->getOptions($query, $payload));
    }

    /**
     * @param null $query
     * @param null $payload
     * @return array[]
     */
    private function getOptions($query = null, $payload = null): array
    {
        $options = [
            'headers' => [
                'AuthToken' => $this->getConfig()['pluginConfig']['taxdooApiToken'],
            ],
        ];
        if ($query !== null) {
            $options['query'] = $query;
        }

        if ($payload !== null) {
            $options['json'] = $payload;
        }

        return $options;
    }

    /**
     * @param string $endpoint
     * @return string
     */
    private function getUri(string $endpoint): string
    {
        $baseUri = $this->getConfig()['pluginConfig']['taxdooSandbox'] ?
            $this->getConfig()['sandboxUrl'] : $this->getConfig()['baseUrl'];

        return rtrim($baseUri, '/') . '/' . ltrim($endpoint, '/');
    }

    /**
     * @return array
     */
    private function getConfig(): array
    {
        if (!isset($this->config)) {
            $this->config = $this->configuration->getConfig();
        }

        return $this->config;
    }

    /**
     * @return Client
     */
    private function getClient(): Client
    {
        if (!isset($this->client)) {
            $this->client = new Client([
                'connect_timeout' => $this->getConfig()['connect_timeout'],
                'timeout' => $this->getConfig()['timeout'],
                'synchronous' => $this->getConfig()['synchronous'],
            ]);
        }

        return $this->client;
    }
}