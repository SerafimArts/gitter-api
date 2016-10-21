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
     * @param AdapterInterface $adapter
     */
    public function __construct(Client $client, AdapterInterface $adapter)
    {
        $this->client = $client;
        $this->adapter = $adapter;
    }

    /**
     * @param string $instanceof
     * @return bool
     */
    protected function adapterAre(string $instanceof)
    {
        return $this->adapter instanceof $instanceof;
    }

    /**
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function currentUser(): array
    {
        if (!array_key_exists($this->client->token(), self::$currentUserId)) {
            $response = $this->sync(Route::get('user')->toApi());
            $userId   = $response[0] ?? null;

            if ($userId === null) {
                throw new \InvalidArgumentException('Broken request. Can not fetch current authenticated user');
            }

            self::$currentUserId[$this->client->token()] = $userId;
        }

        return self::$currentUserId[$this->client->token()];
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
     * @param Route $route
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function sync(Route $route)
    {
        return $this->client->adapter(AdapterInterface::TYPE_SYNC)
            ->request($route);
    }

    /**
     * @param Route $route
     * @return \React\Promise\PromiseInterface|\GuzzleHttp\Promise\PromiseInterface
     * @throws \InvalidArgumentException
     */
    protected function async(Route $route)
    {
        return $this->client->adapter(AdapterInterface::TYPE_ASYNC)
            ->request($route);
    }

    /**
     * @param Route $route
     * @return mixed
     * @throws \InvalidArgumentException
     */
    protected function stream(Route $route)
    {
        return $this->client->adapter(AdapterInterface::TYPE_STREAM)
            ->request($route);
    }
}