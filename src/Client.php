<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Gitter;

use Gitter\Adapters\AdapterInterface;
use Gitter\Adapters\HttpAdapter;
use Gitter\Adapters\StreamAdapter;
use Gitter\Adapters\StreamAdapterInterface;
use Gitter\Adapters\SyncAdapterInterface;
use Gitter\Resources\Groups;
use Gitter\Resources\Messages;
use Gitter\Resources\Rooms;
use Gitter\Resources\Users;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;

/**
 * Class Client
 * @package Gitter
 *
 * @property-read Rooms $rooms
 * @property-read Users $users
 * @property-read Messages $messages
 * @property-read Groups $groups
 *
 * @property string $token
 * @property LoggerInterface|null $logger
 * @property LoopInterface $loop
 *
 */
class Client
{
    /**
     * @var string
     */
    const VERSION = '4.0.7';

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
     * @var int
     */
    private $retries = 100;

    /**
     * @var HttpAdapter|null
     */
    private $http;

    /**
     * @var StreamAdapter|null
     */
    private $streaming;

    /**
     * Client constructor.
     * @param string $token
     * @param LoggerInterface $logger
     */
    public function __construct(string $token, LoggerInterface $logger = null)
    {
        $this->token = $token;
        $this->loop  = Factory::create();

        if (null === ($this->logger = $logger)) {
            $this->logger = new NullLogger();
        }
    }

    /**
     * @param string $token
     * @return Client
     */
    public function updateToken(string $token): Client
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @param int $count
     * @return Client
     */
    public function retries(int $count): Client
    {
        $this->retries = $count;

        return $this;
    }

    /**
     * @return int
     */
    public function getRetriesCount(): int
    {
        return $this->retries;
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->loop->stop();
        $this->storage = [];
    }

    /**
     * @return SyncAdapterInterface|AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function viaHttp(): SyncAdapterInterface
    {
        if ($this->http === null) {
            $this->http = new HttpAdapter($this);
        }

        return $this->http;
    }

    /**
     * @return StreamAdapterInterface|AdapterInterface
     */
    public function viaStream(): StreamAdapterInterface
    {
        if ($this->streaming === null) {
            $this->streaming = new StreamAdapter($this, $this->loop);
        }

        return $this->streaming;
    }

    /**
     * @return void
     */
    public function connect()
    {
        $this->loop->run();
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

    /**
     * @param string $resource
     * @return Groups|Messages|Rooms|Users|null|LoggerInterface|LoopInterface|string
     */
    public function __get(string $resource)
    {
        $resolve = function (string $resource) {
            switch ($resource) {
                // == RESOURCES ==
                case 'users':
                    return new Users($this);
                case 'groups':
                    return new Groups($this);
                case 'messages':
                    return new Messages($this);
                case 'rooms':
                    return new Rooms($this);

                // == COMMON ===
                case 'loop':
                    return $this->loop();
                case 'token':
                    return $this->token();
                case 'logger':
                    return $this->logger();
            }

            return null;
        };

        if (! isset($this->storage[$resource])) {
            $this->storage[$resource] = $resolve($resource);
        }

        return $this->storage[$resource];
    }

    /**
     * @param string $name
     * @param $value
     * @return void
     */
    public function __set(string $name, $value)
    {
        switch ($name) {
            // == COMMON ===
            case 'loop':
                $this->loop($value);
                break;

            case 'token':
                $this->token($value);
                break;

            case 'logger':
                $this->logger($value);
                break;

            default:
                $this->{$name} = $value;
        }
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
     * @return array
     * @throws \Throwable
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function authUser(): array
    {
        return $this->users->current();
    }

    /**
     * @return string
     * @throws \Throwable
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function authId(): string
    {
        return $this->users->currentUserId();
    }

    /**
     * @param string $name
     * @throws \LogicException
     */
    public function __unset(string $name)
    {
        switch ($name) {
            case 'logger':
                $this->logger = null;
                break;
            case 'loop':
                throw new \LogicException('Can not remove EventLoop');
            case 'token':
                throw new \LogicException('Can not remove token value.');
            case 'users':
            case 'groups':
            case 'messages':
            case 'rooms':
                throw new \LogicException('Resource ' . $name . ' can not be removed');
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        if (\in_array($name, ['users', 'groups', 'messages', 'rooms', 'loop', 'token'], true)) {
            return true;
        }

        if ($name === 'logger') {
            return $this->logger !== null;
        }

        return property_exists($this, $name) && $this->{$name} !== null;
    }
}
