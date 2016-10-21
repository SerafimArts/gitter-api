<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter;

use Monolog\Logger;
use Psr\Log\NullLogger;
use Gitter\Support\Loggable;
use Gitter\Resources\Facade;
use Psr\Log\LoggerInterface;
use React\EventLoop\LoopInterface;
use Gitter\ClientAdapter\SyncBuzzAdapter;
use Gitter\ClientAdapter\AsyncBuzzAdapter;
use Gitter\ClientAdapter\AdapterInterface;
use Gitter\ClientAdapter\StreamBuzzAdapter;
use React\EventLoop\Factory as LoopFactory;


/**
 * Class Client
 * @package Gitter
 */
class Client implements Loggable
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
     * @var array|AdapterInterface[]
     */
    private $defaultAdapters = [
        AdapterInterface::TYPE_SYNC   => SyncBuzzAdapter::class,
        AdapterInterface::TYPE_ASYNC  => AsyncBuzzAdapter::class,
        AdapterInterface::TYPE_STREAM => StreamBuzzAdapter::class,
    ];

    /**
     * Client constructor.
     * @param string $token
     * @param LoggerInterface $logger
     */
    public function __construct(string $token, LoggerInterface $logger = null)
    {
        $this->token = $token;
        $this->loop = LoopFactory::create();

        if ($logger === null) {
            $logger = new NullLogger();
        }

        $this->logger = $logger;

        $this->logger->info(sprintf('Gitter Client: %s', static::VERSION));
    }

    /**
     * @param string $message
     * @param int $level
     * @return Loggable|$this
     */
    public function log(string $message, int $level = Logger::INFO): Loggable
    {
        $this->logger->log($level, $message);

        return $this;
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
     * @return Facade
     */
    public function through(string $name): Facade
    {
        if (!array_key_exists($name, $this->adapters)) {
            $this->logger->info(sprintf('Creating \\%s::class adapter', $name));
            $this->adapters[$name] = new $name($this);
        }

        return new Facade($this, $this->adapters[$name]);
    }

    /**
     * @param string $name
     * @param string|null $adapter
     * @return Facade
     * @throws \InvalidArgumentException
     */
    public function adapter(string $name, string $adapter = null): Facade
    {
        if (!array_key_exists($name, $this->defaultAdapters)) {
            throw new \InvalidArgumentException('Adapter type ' . $name . ' does not exists');
        }

        if ($adapter !== null) {
            $this->defaultAdapters[$name] = $adapter;
        }

        return $this->through($this->defaultAdapters[$name]);
    }
}