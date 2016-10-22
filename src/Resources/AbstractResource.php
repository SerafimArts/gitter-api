<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Resources;

use Gitter\Route;
use Gitter\Client;
use Gitter\ClientAdapter\AdapterInterface;
use Gitter\ClientAdapter\SyncAdapterInterface;
use Serafim\Properties\Properties;

/**
 * Class AbstractResource
 * @package Gitter\Resources
 *
 * @property-read $this|AbstractResource $sync
 * @property-read $this|AbstractResource $async
 * @property-read $this|AbstractResource $stream
 */
abstract class AbstractResource implements ResourceInterface
{
    use Properties;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private static $currentUserId = [];

    /**
     * @var AdapterInterface|null
     */
    private $adapter;

    /**
     * AbstractResource constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function currentUser(): array
    {
        if (!array_key_exists($this->client->token, self::$currentUserId)) {
            $response = $this->using(AdapterInterface::TYPE_SYNC)->request(Route::get('user')->toApi());
            $userId   = $response[0] ?? null;

            if ($userId === null) {
                throw new \InvalidArgumentException('Broken request. Can not fetch current authenticated user');
            }

            self::$currentUserId[$this->client->token] = $userId;
        }

        return self::$currentUserId[$this->client->token];
    }

    /**
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function getSync()
    {
        $this->adapter = $this->using(AdapterInterface::TYPE_SYNC);

        return $this;
    }

    /**
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function getAsync()
    {
        $this->adapter = $this->using(AdapterInterface::TYPE_ASYNC);

        return $this;
    }

    /**
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function getStream()
    {
        $this->adapter = $this->using(AdapterInterface::TYPE_STREAM);

        return $this;
    }

    /**
     * @param Route $route
     * @return mixed
     */
    protected function fetch(Route $route)
    {
        $adapter = $this->adapter;

        if (!($this->adapter instanceof SyncAdapterInterface)) {
            $this->resetAdapter();
        }

        if ($adapter === null) {
            $adapter = $this->adapter;
        }

        return $adapter->request($route);
    }

    /**
     * @param string $type
     * @return AdapterInterface
     * @throws \InvalidArgumentException
     */
    protected function using(string $type)
    {
        return $this->client->adapters->using($type);
    }

    /**
     * @return AdapterInterface
     */
    private function resetAdapter(): AdapterInterface
    {
        try {
            $this->adapter = $this->client->adapters->using(AdapterInterface::TYPE_SYNC);
        } catch (\Throwable $e) {
            // Adapters valid ever
        }

        return $this->adapter;
    }
}