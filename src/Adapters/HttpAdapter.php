<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Gitter\Adapters;

use Gitter\Client;
use Gitter\Route;
use GuzzleHttp\Client as Guzzle;
use Psr\Http\Message\ResponseInterface;

/**
 * Class HttpAdapter
 * @package Gitter\Adapters
 */
class HttpAdapter extends AbstractClient implements SyncAdapterInterface
{
    /**
     * @var Guzzle
     */
    private $guzzle;

    /**
     * @var Client
     */
    private $client;

    /**
     * HttpAdapter constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client  = $client;
        $this->options = $this->injectToken($client, []);
        $this->guzzle  = new Guzzle($this->options);
    }

    /**
     * @param Client $client
     * @param array $options
     * @return array
     */
    private function injectToken(Client $client, array $options)
    {
        $options['headers'] = array_merge(
            $options['headers'] ?? [],
            $this->buildHeaders($client)
        );

        return $options;
    }

    /**
     * @param Route $route
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function request(Route $route): array
    {
        list($method, $uri) = [$route->method(), $route->build()];
        $options = $this->prepareRequestOptions($route);

        // Log request
        $this->debugLog($this->client, ' -> ' . $method . ' ' . $uri);
        if ($options['body'] ?? false) {
            $this->debugLog($this->client, '    -> body ' . $options['body']);
        }

        // End log request
        $response = $this->guzzle->request($method, $uri, $options);

        // Log response
        $this->debugLog($this->client, ' <- ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());
        $this->debugLog($this->client, '   <- ' . (string)$response->getBody());
        // End log response

        return $this->parseResponse($response);
    }

    /**
     * @param Route $route
     * @return array
     */
    private function prepareRequestOptions(Route $route): array
    {
        $options = [];

        if ($route->method() !== 'GET' && $route->getBody() !== null) {
            $options['body'] = $route->getBody();
        }

        return array_merge($this->options, $options);
    }

    /**
     * @param ResponseInterface $response
     * @return array
     * @throws \RuntimeException
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $data = json_decode((string)$response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(json_last_error_msg());
        }

        return $data;
    }
}
