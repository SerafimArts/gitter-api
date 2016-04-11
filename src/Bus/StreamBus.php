<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 11.04.2016 13:17
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Bus;

use Gitter\Client;
use Gitter\Http\JsonBuffer;
use Gitter\Http\Uri;
use GuzzleHttp\Tests\Psr7\Str;
use React\Dns\Resolver\Factory as DnsResolver;
use React\Dns\Resolver\Resolver;
use React\EventLoop\Factory as EventLoop;
use React\EventLoop\LoopInterface;
use React\HttpClient\Request;
use React\HttpClient\Client as ReactClient;
use React\HttpClient\Factory as HttpClient;
use React\HttpClient\Response;

/**
 * Class StreamBus
 * @package Gitter\Bus
 */
class StreamBus implements Bus
{
    const STREAM_URI = 'https://stream.gitter.im/{version}/';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var \React\Dns\Resolver\Resolver
     */
    private $dnsResolver;

    /**
     * @var ReactClient
     */
    private $react;
    
    /**
     * StreamBus constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->loop = EventLoop::create();

        $dns = (new DnsResolver())->createCached('8.8.8.8', $this->loop);
        $this->updateDnsResolver($dns);
    }

    /**
     * @param Resolver $resolver
     * @return $this|StreamBus|Bus
     */
    public function updateDnsResolver(Resolver $resolver) : Bus
    {
        $this->dnsResolver = $resolver;
        $this->react = (new HttpClient())->create($this->loop, $this->dnsResolver);

        return $this;
    }

    /**
     * @param string $roomId
     * @param \Closure $callback
     * @throws \LogicException
     * @return $this
     */
    public function onMessage(string $roomId, \Closure $callback)
    {
        $url = $this->getUrl($roomId, 'chatMessages');

        $request = $this->react->request('GET', $url, $this->getHeaders());

        $this->stream($request, $callback);

        return $this;
    }

    /**
     * @param string $roomId
     * @param \Closure $callback
     * @throws \LogicException
     * @return $this
     */
    public function onEvent(string $roomId, \Closure $callback)
    {
        $url = $this->getUrl($roomId, 'events');

        $request = $this->react->request('GET', $url, $this->getHeaders());

        $this->stream($request, $callback);

        return $this;
    }

    /**
     * @param Request $request
     * @param \Closure $callback
     * @throws \LogicException
     * @return void
     */
    private function stream(Request $request, \Closure $callback)
    {
        $buffer = (new JsonBuffer())->subscribe($callback);

        $request->on('response', function (Response $response) use ($buffer) {
            $response->on('data', function ($data, Response $response) use ($buffer) {
                $text = (string)$data;

                if ($text !== "\n" && trim($text) !== '[]') {
                    $buffer->push($text);
                }
            });
        });

        $request->on('end', function () use ($buffer) {
            $buffer->clear();
        });

        $request->on('error', function ($exception) use ($buffer) {
            $buffer->clear();
            throw $exception;
        });

        $request->end();
    }

    /**
     * @param string $roomId
     * @param string $resource
     * @return string
     */
    private function getUrl(string $roomId, string $resource) : string
    {
        $builder = new Uri('rooms/{roomId}/{resource}', static::STREAM_URI);
        $builder->addArgument('roomId', $roomId);
        $builder->addArgument('resource', $resource);

        return $builder->build();
    }

    /**
     * @return array
     */
    private function getHeaders() : array
    {
        return [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Authorization' => 'Bearer ' . $this->client->getToken(),
        ];
    }

    /**
     * @return LoopInterface
     */
    public function getEventLoop() : LoopInterface
    {
        return $this->loop;
    }

    /**
     * @return void
     */
    public function run()
    {
        $this->getEventLoop()->run();
    }
}
