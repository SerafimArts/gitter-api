<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter;

use Gitter\ClientAdapter\AdapterInterface;
use Gitter\ClientAdapter\HttpClient;
use Monolog\Logger;
use Psr\Log\NullLogger;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use React\EventLoop\Factory as LoopFactory;

/**
 * Class Client
 * @package Gitter
 */
class Client
{
    /**
     * @var string
     */
    const VERSION = '3.0.0';

    /**
     * @var string
     */
    private $token;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array|AdapterInterface[]
     */
    private $adapters = [];

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

        $this->logger->info(sprintf('Gitter Client: %s', static::VERSION));
    }

    /**
     * @return HttpClient|AdapterInterface
     */
    public function http(): HttpClient
    {
        return $this->getAdapter(HttpClient::class, function() {
            return new HttpClient($this);
        });
    }

    /**
     * @param string $message
     * @param int $level
     * @return void
     */
    public function log(string $message, int $level = Logger::INFO)
    {
        $this->logger->log($level, $message);
    }

    /**
     * @return LoopInterface
     */
    public function loop(): LoopInterface
    {
        return $this->loop;
    }

    /**
     * @return string
     */
    public function token(): string
    {
        return $this->token;
    }

    /**
     * @return void
     */
    public function connect()
    {
        $this->logger->info('Starting');
        $this->loop->run();
    }

    /**
     * @param string $name
     * @param \Closure $resolver
     * @return AdapterInterface
     */
    private function getAdapter(string $name, \Closure $resolver): AdapterInterface
    {
        if (!array_key_exists($name, $this->adapters)) {
            $this->logger->info(sprintf('Creating \\%s::class adapter', $name));
            $this->adapters[$name] = $resolver();
        }

        return $this->adapters[$name];
    }
}