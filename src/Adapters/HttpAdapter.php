<?php declare(strict_types=1);
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Adapters;

use Gitter\Client;
use Gitter\Route;
use GuzzleHttp\Client as Guzzle;
use Monolog\Logger;
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
        $this->client = $client;
        $this->setOptions();
    }

    /**
     * @param array $options
     * @return AdapterInterface
     */
    public function setOptions(array $options = []): AdapterInterface
    {
        parent::setOptions($this->injectToken($this->client, $options));

        $this->guzzle = new Guzzle($this->options);

        return $this;
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
        $this->client->log(' -> ' . $method . ' ' . $uri . "\n body: " . ($options['body'] ?? ''), Logger::DEBUG);

        $response = $this->guzzle->request($method, $uri, $options);

        // Log response
        $this->client->log(' <- ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase(), Logger::DEBUG);
        $this->client->log('   <- ' . (string)$response->getBody(), Logger::DEBUG);

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

        return $options;
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