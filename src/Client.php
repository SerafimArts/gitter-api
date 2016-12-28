<?php declare(strict_types = 1);
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter;

use Gitter\Adapters\AdapterInterface;
use Gitter\Adapters\HttpAdapter;
use Gitter\Adapters\StreamAdapter;
use Gitter\Adapters\StreamAdapterInterface;
use Gitter\Adapters\SyncAdapterInterface;
use Gitter\Resources\Groups;
use Gitter\Resources\Messages;
use Gitter\Resources\ResourceInterface;
use Gitter\Resources\Rooms;
use Gitter\Resources\Users;
use Gitter\Support\Loggable;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

/**
 * Class Client
 * @package Gitter
 */
class Client implements Loggable
{
    /**
     * @var string
     */
    const VERSION = '4.0.0';

    /**
     * @var string
     */
    protected $token;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var array
     */
    private $storage = [];

    /**
     * Client constructor.
     * @param string $token
     */
    public function __construct(string $token, LoggerInterface $logger = null)
    {
        $this->token = $token;
        $this->loop = Factory::create();

        if (null === ($this->logger = $logger)) {
            $this->logger = new NullLogger();
        }
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
     * @param LoggerInterface|null $logger
     * @return LoggerInterface
     */
    public function logger(LoggerInterface $logger = null): LoggerInterface
    {
        if ($logger !== null) {
            $this->logger = $logger;
        }

        return $this->logger;
    }

    /**
     * @param string|null $token
     * @return string
     */
    public function token(string $token = null): string
    {
        if ($token !== null) {
            $this->token = $token;
        }

        return $this->token;
    }

    /**
     * @return SyncAdapterInterface|AdapterInterface
     */
    public function viaHttp(): SyncAdapterInterface
    {
        return new HttpAdapter($this);
    }

    /**
     * @return StreamAdapterInterface|AdapterInterface
     */
    public function viaStream(): StreamAdapterInterface
    {
        return new StreamAdapter($this, $this->loop);
    }

    /**
     * @param LoopInterface|null $loop
     * @return LoopInterface
     */
    public function loop(LoopInterface $loop = null): LoopInterface
    {
        if ($loop !== null) {
            $this->loop = $loop;
        }

        return $this->loop;
    }

    /**
     * @return void
     */
    public function connect()
    {
        $this->loop->run();
    }

    /**
     * @return Groups
     */
    public function groups(): Groups
    {
        return new Groups($this);
    }

    /**
     * @return Messages
     */
    public function messages(): Messages
    {
        return new Messages($this);
    }

    /**
     * @return Rooms
     */
    public function rooms(): Rooms
    {
        return new Rooms($this);
    }

    /**
     * @return Users
     */
    public function users(): Users
    {
        return new Users($this);
    }

    /**
     * @param string $hookId
     * @return WebHook
     * @throws \InvalidArgumentException
     */
    public function notify(string $hookId): WebHook
    {
        return new WebHook($this, $hookId);
    }
}
