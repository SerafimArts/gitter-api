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
use Gitter\ClientAdapter\SyncBuzzAdapter;
use Gitter\ClientAdapter\AdapterInterface;

/**
 * Class AbstractResource
 * @package Gitter\Resources
 */
abstract class AbstractResource implements ResourceInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var array
     */
    private static $currentUserId = [];

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * AbstractResource constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->adapter = new SyncBuzzAdapter($client);
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function currentUser(): array
    {
        if (!array_key_exists($this->client->token, self::$currentUserId)) {
            $response = $this->sync(Route::get('user')->toApi());
            $userId   = $response[0] ?? null;

            if ($userId === null) {
                throw new \InvalidArgumentException('Broken request. Can not fetch current authenticated user');
            }

            self::$currentUserId[$this->client->token] = $userId;
        }

        return self::$currentUserId[$this->client->token];
    }

    /**
     * @param Route $route
     * @return mixed
     */
    protected function fetch(Route $route)
    {
        return $this->adapter->request($route);
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
     * @param Route $route
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function sync(Route $route)
    {
        return $this->using(AdapterInterface::TYPE_SYNC)->request($route);
    }

    /**
     * @param Route $route
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function async(Route $route)
    {
        return $this->using(AdapterInterface::TYPE_ASYNC)->request($route);
    }

    /**
     * @param Route $route
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function stream(Route $route)
    {
        return $this->using(AdapterInterface::TYPE_STREAM)->request($route);
    }
}