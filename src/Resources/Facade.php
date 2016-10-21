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
use Psr\Http\Message\ResponseInterface;
use Gitter\ClientAdapter\AdapterInterface;

/**
 * Class Facade
 * @package Gitter\Resources
 *
 * @property-read Groups $groups
 * @property-read Rooms $rooms
 */
class Facade implements ResourceInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var AdapterInterface
     */
    private $adapter;

    /**
     * @var array|ResourceInterface[]
     */
    private $resources = [];

    /**
     * ResourceFacade constructor.
     * @param Client $client
     * @param AdapterInterface $adapter
     */
    public function __construct(Client $client, AdapterInterface $adapter)
    {
        $this->client = $client;
        $this->adapter = $adapter;
    }

    /**
     * @param Route $route
     * @return mixed
     */
    public function request(Route $route)
    {
        return $this->adapter->request($route);
    }

    /**
     * @param string $name
     * @return ResourceInterface
     * @throws \InvalidArgumentException
     */
    public function __get(string $name): ResourceInterface
    {
        if (!array_key_exists($name, $this->resources)) {
            if (!isset($this->$name)) {
                throw new \InvalidArgumentException('Resource ' . $name . ' does not exists');
            }

            /** @var ResourceInterface $class */
            $class = $this->resourceName($name);

            $this->resources[$name] = new $class($this->client, $this->adapter);
        }

        return $this->resources[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return class_exists($this->resourceName($name));
    }

    /**
     * @param string $name
     * @param $value
     * @throws \LogicException
     */
    public function __set(string $name, $value)
    {
        throw new \LogicException(__CLASS__ . ' are immutable');
    }

    /**
     * @param string $name
     * @throws \LogicException
     */
    public function __unset(string $name)
    {
        throw new \LogicException(__CLASS__ . ' are immutable');
    }

    /**
     * @param string $name
     * @return string
     */
    private function resourceName(string $name)
    {
        /** @var ResponseInterface $class */
        return __NAMESPACE__ . '\\' . ucfirst($name);
    }
}