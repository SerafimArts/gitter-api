<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter;

use Psr\Log\NullLogger;
use Gitter\Api\RestApi;
use Gitter\Http\HttpClient;
use Psr\Log\LoggerInterface;
use Gitter\Api\ApiInterface;
use Gitter\Http\AsyncHttpClient;
use React\EventLoop\LoopInterface;
use Illuminate\Container\Container;
use React\EventLoop\Factory as LoopFactory;

/**
 * Class Client
 * @package Gitter
 *
 * @property-read ApiInterface|RestApi $http
 * @property-read ApiInterface|RestApi $async
 */
class Client extends Container
{
    const CONNECTION_HTTP = 'http';
    const CONNECTION_STREAM = 'stream';
    const CONNECTION_ASYNC = 'async';

    /**
     * @var string
     */
    private $token;

    /**
     * @var \React\EventLoop\ExtEventLoop|\React\EventLoop\LibEventLoop|\React\EventLoop\LibEvLoop|\React\EventLoop\StreamSelectLoop
     */
    private $loop;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Client constructor.
     * @param string $token
     * @param LoggerInterface $logger
     */
    public function __construct(string $token, LoggerInterface $logger = null)
    {
        $this->token = $token;
        $this->loop  = LoopFactory::create();

        if ($logger === null) {
            $logger = new NullLogger();
        }

        $this->logger = $logger;

        $this->registerCoreInstances();

    }

    /**
     * @return \React\EventLoop\ExtEventLoop|\React\EventLoop\LibEventLoop|\React\EventLoop\LibEvLoop|\React\EventLoop\StreamSelectLoop
     */
    public function getLoop()
    {
        return $this->loop;
    }

    /**
     * @return void
     */
    private function registerCoreInstances()
    {
        $this->instance(static::class, $this);
        $this->instance(LoopInterface::class, $this->loop);

        $this->instance(LoggerInterface::class, $this->logger);

        $this->singleton(HttpClient::class);
        $this->singleton(AsyncHttpClient::class);
    }

    /**
     * @return string
     */
    public function getToken() : string
    {
        return $this->token;
    }

    /**
     * @param string $key
     * @return ApiInterface
     * @throws \InvalidArgumentException
     */
    public function __get($key)
    {
        switch ($key) {
            case static::CONNECTION_HTTP:
                return $this->make(RestApi::class, [
                    'client' => $this->make(HttpClient::class)
                ]);

            case static::CONNECTION_ASYNC:
                return $this->make(RestApi::class, [
                    'client' => $this->make(AsyncHttpClient::class)
                ]);

            //case static::CONNECTION_STREAM:
                //return $this->make(StreamHttpConnection::class);
        }

        throw new \InvalidArgumentException(sprintf('Property %s::$%s not found', static::class, $key));
    }

    /**
     * @return void
     */
    public function connect()
    {
        $this->loop->run();
    }
}