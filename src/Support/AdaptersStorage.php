<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

use Gitter\Client;
use Gitter\ClientAdapter\SyncBuzzAdapter;
use Gitter\ClientAdapter\AdapterInterface;
use Gitter\ClientAdapter\AsyncBuzzAdapter;
use Gitter\ClientAdapter\StreamBuzzAdapter;

/**
 * Class AdaptersStorage
 * @package Gitter\Support
 */
class AdaptersStorage
{
    /**
     * @var array|AdapterInterface[]
     */
    private $defaultAdapters = [
        AdapterInterface::TYPE_SYNC   => SyncBuzzAdapter::class,
        AdapterInterface::TYPE_ASYNC  => AsyncBuzzAdapter::class,
        AdapterInterface::TYPE_STREAM => StreamBuzzAdapter::class,
    ];

    /**
     * @var array|AdapterInterface[]
     */
    private $adapters = [];

    /**
     * @var Client
     */
    private $client;

    /**
     * AdaptersStorage constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $type
     * @return AdapterInterface
     * @throws \InvalidArgumentException
     */
    public function using(string $type): AdapterInterface
    {
        if (!isset($this->defaultAdapters[$type])) {
            throw new \InvalidArgumentException(sprintf('Invalid adapter type %s', $type));
        }

        return $this->through($this->defaultAdapters[$type]);
    }

    /**
     * @param string $type
     * @param AdapterInterface $adapter
     * @return $this
     */
    public function set(string $type, AdapterInterface $adapter)
    {
        $this->adapters[$type] = $adapter;

        return $this;
    }

    /**
     * @param string $class
     * @return AdapterInterface|mixed
     */
    public function through(string $class)
    {
        if (!isset($this->adapters[$class])) {
            $this->adapters[$class] = new $class($this->client);
        }

        return $this->adapters[$class];
    }
}
