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
use Gitter\Resources\Rooms;
use Gitter\Resources\Users;
use Gitter\Support\Loggable;
use Psr\Log\LoggerInterface;
use Gitter\Resources\Common;
use Gitter\Resources\Groups;
use Gitter\Resources\Messages;
use Serafim\Properties\Properties;
use React\EventLoop\LoopInterface;
use Gitter\Support\AdaptersStorage;
use Gitter\Resources\ResourceInterface;
use React\EventLoop\Factory as LoopFactory;

/**
 * Class Client
 * @package Gitter
 *
 * @property-read string $token
 * @property-read LoopInterface $loop
 * @property-read AdaptersStorage $adapters
 *
 * @property-read Common|ResourceInterface $request
 *
 * @property-read Rooms|ResourceInterface $rooms
 * @property-read Users|ResourceInterface $users
 * @property-read Groups|ResourceInterface $groups
 * @property-read Messages|ResourceInterface $messages
 */
class Client implements Loggable
{
    use Properties;

    /**
     * @var string
     */
    const VERSION = '3.0.0';

    /**
     * @var string
     */
    protected $token;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var AdaptersStorage
     */
    protected $adapters;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var array|ResourceInterface[]
     */
    private $resources = [];

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

        $this->logger   = $logger;
        $this->adapters = new AdaptersStorage($this);

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
     * @return void
     */
    public function connect()
    {
        $this->logger->info('Starting');
        $this->loop->run();
    }

    /**
     * @return void
     */
    public function disconnect()
    {
        $this->logger->info('Stopping');
        $this->loop->stop();
    }

    /**
     * @return Messages|ResourceInterface
     */
    protected function getMessages(): Messages
    {
        return $this->resource(Messages::class);
    }

    /**
     * @return Groups|ResourceInterface
     */
    protected function getGroups(): Groups
    {
        return $this->resource(Groups::class);
    }

    /**
     * @return Rooms|ResourceInterface
     */
    protected function getRooms(): Rooms
    {
        return $this->resource(Rooms::class);
    }

    /**
     * @return Users|ResourceInterface
     */
    protected function getUsers(): Users
    {
        return $this->resource(Users::class);
    }

    /**
     * @return Common|ResourceInterface
     */
    protected function getRequest(): Common
    {
        return $this->resource(Common::class);
    }

    /**
     * @param string $name
     * @return ResourceInterface
     */
    private function resource(string $name)
    {
        if (!array_key_exists($name, $this->resources)) {
            $this->resources[$name] = new $name($this);
        }

        return $this->resources[$name];
    }
}