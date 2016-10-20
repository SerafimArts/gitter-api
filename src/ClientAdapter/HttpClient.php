<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Gitter\Route;
use Gitter\Client;
use Monolog\Logger;
use React\Promise\Promise;
use React\Promise\Deferred;
use Gitter\Support\JsonStream;
use React\HttpClient\Response;
use React\Dns\Resolver\Resolver;
use Gitter\Exceptions\HttpResponseException;
use React\HttpClient\Factory as HttpFactory;
use React\Dns\Resolver\Factory as DnsFactory;
use React\HttpClient\Client as ReactHttpClient;

/**
 * Class HttpClient
 * @package Gitter\ClientAdapter
 */
class HttpClient implements AdapterInterface
{
    /**
     * @var string
     */
    const DEFAULT_DNS_SERVER = '8.8.8.8';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var Resolver
     */
    private $dnsResolver;

    /**
     * @var ReactHttpClient
     */
    private $httpClient;

    /**
     * HttpAdapter constructor.
     * @param Client $client
     * @param string $dns
     */
    public function __construct(Client $client, string $dns = self::DEFAULT_DNS_SERVER)
    {
        $this->client = $client;
        $this->updateDnsResolver($dns);
        $this->httpClient = (new HttpFactory)->create($client->loop(), $this->dnsResolver);
    }

    /**
     * @param string $dns
     * @return $this|HttpClient
     */
    private function updateDnsResolver(string $dns = self::DEFAULT_DNS_SERVER): HttpClient
    {
        $this->dnsResolver = (new DnsFactory)->createCached($dns, $this->client->loop());

        return $this;
    }

    /**
     * @param Route $route
     * @param array $data
     * @throws \InvalidArgumentException
     * @throws \Gitter\Exceptions\HttpResponseException
     * @return Promise
     */
    public function request(Route $route, array $data = [])
    {
        $url      = $route->build();
        $method   = $route->getMethod();
        $body     = $this->bodyToString($route, $data);
        $headers  = $this->prepareHeaders($route, $body);

        $deferred = new Deferred();
        $json     = new JsonStream();


        $request = $this->httpClient->request($method, $url, $headers, '1.1');
        $request->write($body);

        $request->on('response', function (Response $response) use ($method, $url, $deferred, $json) {
            $message = sprintf('<(http) %s %s [%s]', $method, $url, json_encode($response->getHeaders()));
            $this->client->log($message);

            if ($response->getCode() >= 400) {
                $message = sprintf('Server returns status code %s with message %s', $response->getCode(), $content);
                $this->client->log($message, Logger::ERROR);

                $json->dispose();
                $deferred->reject(new HttpResponseException($message));

                return;
            }

            $this->onResponseData($response, $deferred, $json);
        });

        $request->on('error', function(\Throwable $e) use ($deferred, $json) {
            $json->dispose();
            $deferred->reject($e);
        });

        $message = sprintf('(http)> %s %s [%s] %s', $method, $url, json_encode($headers), $body ? "\n" . $body : '');
        $this->client->log($message);

        $json->on('data', function($data) use ($deferred) {
            $deferred->resolve($data);
        });

        $request->end();

        return $deferred->promise();
    }

    /**
     * @param Response $response
     * @param Deferred $deferred
     * @param JsonStream $json
     */
    private function onResponseData(Response $response, Deferred $deferred, JsonStream $json)
    {
        $response->on('data', function ($data, Response $response) use ($deferred, $json) {
            $content = (string)$data;

            $this->client->log('Input chunk: "' . $content . '"', Logger::DEBUG);
            // $deferred->notify(strlen($content));

            if (trim($content) && $content !== '[]') {
                $this->client->log('Approved chunk');
                $json->push($content);
            } else {
                $this->client->log('Rejected chunk');
            }

            $json->compile();
        });


        $response->on('error', function(\Throwable $e) use ($deferred, $json) {
            $json->dispose();
            $deferred->reject($e);
        });

        $response->on('end', function() use ($json) {
            $json->compile();
        });
    }

    /**
     * @param Route $route
     * @param string $body
     * @return array
     */
    private function prepareHeaders(Route $route, string $body): array
    {
        $headers = [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => sprintf('Bearer %s', $this->client->token())
        ];

        if ($body === '') {
            $headers['Content-Length'] = strlen($body);
        }

        return $headers;
    }

    /**
     * @param Route $route
     * @param array $data
     * @return string
     * @throws \InvalidArgumentException
     */
    private function bodyToString(Route $route, array $data = []): string
    {
        $body = count($data) ? json_encode($data) : '';

        if ($body && $route->getMethod() === 'GET') {
            throw new \InvalidArgumentException('Get request can not contains body');
        }

        return $body;
    }
}