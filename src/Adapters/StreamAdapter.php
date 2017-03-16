<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Gitter\Adapters;

use Clue\React\Buzz\Browser;
use Gitter\Client;
use Gitter\Route;
use Gitter\Support\JsonStream;
use Gitter\Support\Observer;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\ExtEventLoop;
use React\EventLoop\LibEventLoop;
use React\EventLoop\LibEvLoop;
use React\EventLoop\LoopInterface;
use React\EventLoop\StreamSelectLoop;
use React\Promise\Promise;
use React\Stream\ReadableStreamInterface;

/**
 * Class HttpAdapter
 * @package Gitter\Adapters
 */
class StreamAdapter extends AbstractClient implements StreamAdapterInterface
{
    /**
     * @var Browser
     */
    private $browser;

    /**
     * @var ExtEventLoop|LibEventLoop|LibEvLoop|StreamSelectLoop
     */
    private $loop;

    /**
     * @var Client
     */
    private $client;

    /**
     * HttpAdapter constructor.
     * @param Client $client
     * @param LoopInterface $loop
     */
    public function __construct(Client $client, LoopInterface $loop)
    {
        $this->client = $client;
        $this->loop = $loop;
        $this->browser = new Browser($loop);
    }

    /**
     * @return LoopInterface
     */
    public function getEventLoop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * @param Route $route
     * @return Observer
     * @throws \InvalidArgumentException
     */
    public function request(Route $route): Observer
    {
        $observer = new Observer();

        $this->promise($route)->then(function (ResponseInterface $response) use ($observer) {
            $this->onConnect($response, $observer);
        });

        return $observer;
    }

    /**
     * @param Route $route
     * @return Promise
     * @throws \InvalidArgumentException
     */
    private function promise(Route $route): Promise
    {
        list($method, $uri) = [$route->method(), $route->build()];

        // Log request
        $this->debugLog($this->client, ' -> ' . $method . ' ' . $uri);

        return $this->browser
            ->withOptions(['streaming' => true])
            ->{strtolower($method)}($route->build(), $this->buildHeaders($this->client));
    }

    /**
     * @param ResponseInterface $response
     * @param Observer $observer
     */
    private function onConnect(ResponseInterface $response, Observer $observer)
    {
        $json = new JsonStream();

        // Log response
        $this->debugLog($this->client, ' <- ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase());

        /* @var $body ReadableStreamInterface */
        $body = $response->getBody();

        $body->on('data', function ($chunk) use ($json, $observer) {
            // Log response chunk
            $this->debugLog($this->client, '   <- ' . $chunk);

            $json->push($chunk, function ($object) use ($observer) {
                $observer->fire($object);
            });
        });
    }
}
