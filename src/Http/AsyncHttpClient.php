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
use Gitter\Support\Fiber;
use Psr\Log\LoggerInterface;
use React\Promise\Deferred;
use Psr\Log\LoggerAwareTrait;
use React\HttpClient\Response;
use React\Dns\Resolver\Resolver;
use React\Dns\Resolver\Factory as DnsFactory;
use React\HttpClient\Factory as RequestFactory;

/**
 * Class AsyncHttpClient
 * @package Gitter\Connection
 */
class AsyncHttpClient implements HttpClientInterface
{
    use LoggerAwareTrait;

    const HOST = 'https://api.gitter.im/{version}/';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var \React\Dns\Resolver\Resolver
     */
    private $dns;

    /**
     * @var \React\HttpClient\Client
     */
    private $io;

    /**
     * @var array
     */
    private $defaults = [
        'version' => 'v1'
    ];

    /**
     * @var array
     */
    private $headers = [
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

        $this
            ->setDnsResolver((new DnsFactory)->create('8.8.8.8', $client->getLoop()))
            ->setAccessToken($client->getToken());
    }

    /**
     * @param Resolver $resolver
     * @return $this|HttpClientInterface
     */
    public function setDnsResolver(Resolver $resolver) : HttpClientInterface
    {
        $this->dns = $resolver;
        $this->io  = (new RequestFactory)
            ->create($this->client->getLoop(), $this->dns);

        return $this;
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
     * @param Route $route
     * @param string $method
     * @param array $body
     * @return \React\Promise\Promise|\React\Promise\PromiseInterface
     */
    public function request(Route $route, $method = 'GET', array $body = [])
    {
        $deferred = new Deferred();

        $headers = $this->headers;
        $content = json_encode($body);

        if ($body) {
            $headers = array_merge($this->headers, ['Content-Length' => strlen($content)]);
        }

        $request = $this->io->request($method, $route->make(), $headers);

        if ($body) {
            $request->write($content);
        }

        if ($this->logger) {
            $this->logger->info(
                $method . ' ' . $route->make() . "\n" .
                '    Headers: ' . "\n" .
                    json_encode($headers, JSON_PRETTY_PRINT) . "\n" .
                '    Body: ' . "\n" .
                    ($body ? $content : 'null')
            );
        }

        $request->on('response', function (Response $response) use ($deferred) {
            $buffer = '';

            $response->on('data', function ($data, Response $response) use (&$buffer) {
                $buffer .= (string)$data;
            });

            $response->on('end', function($a, $b) use (&$buffer, $deferred) {
                $data = json_decode($buffer, true);


                if (json_last_error() !== JSON_ERROR_NONE) {
                    $deferred->reject(json_last_error_msg());

                } else {
                    if ($this->logger) {
                        $this->logger->info('Response: ' . json_encode($data, JSON_PRETTY_PRINT));
                    }

                    $deferred->resolve(new Fiber($data));
                    $buffer = '';
                }
            });
        });

        $request->end();

        return $deferred->promise();
    }
}