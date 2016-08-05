<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Http;

use Gitter\Client;
use Gitter\Url\Route;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractHttpClient
 * @package Gitter\Http
 */
abstract class AbstractHttpClient implements HttpClientInterface
{
    use LoggerAwareTrait;

    /* protected */ const HOST = 'https://api.gitter.im/{version}/';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $defaults = [
        'version' => 'v1'
    ];

    /**
     * @var array
     */
    protected $headers = [
        'Accept'         => 'application/json',
        'Content-Type'   => 'application/json'
    ];

    /**
     * AsyncHttpClient constructor.
     * @param Client $client
     * @param LoggerInterface $logger
     */
    public function __construct(Client $client, LoggerInterface $logger)
    {
        $this->client = $client;

        $this->setLogger($logger);
        $this->setAccessToken($client->getToken());
    }

    /**
     * @param string $token
     * @return $this|HttpClientInterface
     */
    public function setAccessToken(string $token) : HttpClientInterface
    {
        $this->headers['Authorization'] = sprintf('Bearer %s', $token);
        return $this;
    }

    /**
     * @param string $url
     * @param array $parameters
     * @return Route
     */
    public function route(string $url, array $parameters = []) : Route
    {
        $parameters = array_merge($this->defaults, $parameters);

        return (new Route(static::HOST . $url))->withMany($parameters);
    }

    /**
     * @param string $method
     * @param Route $route
     * @param array $headers
     * @param array $body
     */
    protected function logRequest(string $method, Route $route, array $headers, array $body)
    {

        if ($this->logger) {
            $headersText = json_encode($headers, JSON_PRETTY_PRINT);
            $bodyText    = json_encode($body, JSON_PRETTY_PRINT);

            $this->logger->info(
                $method . ' ' . $route->make() . "\n" .

                '    Headers: ' . "\n" .
                    $headersText . "\n" .

                '    Body: ' . "\n" .
                    $bodyText . "\n"
            );
        }
    }
}