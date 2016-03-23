<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 01.03.2016 17:18
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Http;

use Amp\Artax\Request as OriginalRequest;
use Amp\Artax\Response as OriginalResponse;
use Gitter\Client;

/**
 * Class Request
 * @package Gitter\Http
 */
class Request
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var OriginalRequest
     */
    private $original;

    /**
     * @var Uri
     */
    private $uri;

    /**
     * @var string|null
     */
    private $body;

    /**
     * Request constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->original = new OriginalRequest;
        $this->uri = new Uri;
    }

    /**
     * @param $url
     * @param array $args
     * @return $this
     */
    public function to($url, array $args = [])
    {
        if (is_string($url)) {
            $url = new Uri($url);
        }

        $this->uri
            ->setHost($url->getHost())
            ->setUrl($url->getUrl())
            ->addArguments($url->getArgs())
            ->addArguments($args);

        return $this;
    }

    /**
     * @return Uri
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return null|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string|array|null $body
     * @return $this
     */
    public function setBody($body = null)
    {
        if ($body && !is_string($body)) {
            $body = json_encode($body);
        }

        $this->body = $body;

        return $this;
    }

    /**
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     */
    public function get()
    {
        return $this->send('GET');
    }

    /**
     * @param string $method
     * @param string|array|null $body
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     */
    public function wrap(string $method = 'GET', $body = null)
    {
        $response = $this->send($method, $body);

        $response = \Amp\pipe($response, function (OriginalResponse $response) {
            return new Response($this, $response);
        });

        return new Promise($response);
    }

    /**
     * @param string $method
     * @param null $body
     * @return \Amp\Promise
     */
    public function send(string $method = 'GET', $body = null)
    {
        $this->setBody($body);

        $headers = [
            'Accept'         => 'application/json',
            'Content-Type'   => 'application/json',
            'Authorization'  => sprintf('Bearer %s', $this->client->getToken()),
        ];

        $request = $this->original
            ->setUri($this->uri->build())
            ->setProtocol('1.1');

        if ($body !== null) {
            $request = $request->setBody($this->body);
            $headers['Content-Length'] = strlen($this->body ?: 0);
        }

        $request = $request
            ->setAllHeaders($headers)
            ->setMethod($method);

        return $this->client
            ->getArtaxClient()
            ->request($request);
    }

    /**
     * @param string|array|null $body
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     */
    public function post($body = null)
    {
        return $this->wrap('POST', $body);
    }

    /**
     * @param string|array|null $body
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     */
    public function put($body = null)
    {
        return $this->wrap('PUT', $body);
    }

    /**
     * @param string|array|null $body
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     */
    public function delete($body = null)
    {
        return $this->wrap('DELETE', $body);
    }
}
