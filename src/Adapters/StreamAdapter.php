<?php declare(strict_types = 1);
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Adapters;

use Clue\React\Buzz\Browser;
use Clue\React\Buzz\Io\Sender;
use Clue\React\Buzz\Message\MessageFactory;
use Gitter\Client;
use Gitter\Route;
use Gitter\Support\JsonStream;
use Gitter\Support\Observer;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\ExtEventLoop;
use React\EventLoop\Factory as EventLoop;
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
     * @var Sender
     */
    private $sender;

    /**
     * @var MessageFactory
     */
    private $messages;

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
        $this->sender = Sender::createFromLoop($loop);
        $this->messages = new MessageFactory();

        $this->browser = new Browser($loop, $this->sender, $this->messages);
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
        $this->client->log(' -> ' . $method . ' ' . $uri, Logger::DEBUG);

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
        $this->client->log(' <- ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase(), Logger::DEBUG);

        /* @var $body ReadableStreamInterface */
        $body = $response->getBody();

        $body->on('data', function ($chunk) use ($json, $observer) {
            // Log response chunk
            $this->client->log('   <- ' . $chunk, Logger::DEBUG);

            $json->push($chunk, function ($object) use ($observer) {
                $observer->fire($object);
            });
        });
    }
}
