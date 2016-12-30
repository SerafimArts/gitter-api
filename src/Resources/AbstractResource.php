<?php declare(strict_types=1);
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Resources;

use Gitter\Adapters\AdapterInterface;
use Gitter\Adapters\HttpAdapter;
use Gitter\Adapters\StreamAdapter;
use Gitter\Route;
use Gitter\Client;
use Gitter\Support\Observer;
use Serafim\Evacuator\Evacuator;
use GuzzleHttp\Exception\RequestException;

/**
 * Class AbstractResource
 * @package Gitter\Resources
 */
abstract class AbstractResource implements ResourceInterface
{
    /**
     * @var null
     */
    private $client;

    /**
     * AbstractResource constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @return Client
     */
    protected function client(): Client
    {
        return $this->client;
    }

    /**
     * @param Route $route
     * @return array|mixed
     * @throws \GuzzleHttp\Exception\RequestException
     * @throws \Throwable
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    protected function fetch(Route $route): array
    {
        $rescue = (new Evacuator(function () use ($route) {
            return (array)$this->viaHttp()->request($route);
        }))
            ->onError(function (RequestException $e) {
                $this->client->logger->error($e->getMessage());

                // Throws request exception if SSL error
                if (false !== strpos($e->getMessage(), 'SSL certificate problem')) {
                    throw $e;
                }
            })
            ->onError(function (\Exception $e) {
                $this->client->logger->error($e->getMessage());
            })
            ->retries($this->client->getRetriesCount())
            ->invoke();

        return $rescue;
    }

    /**
     * @param Route $route
     * @return Observer
     * @throws \Throwable
     * @throws \InvalidArgumentException
     */
    protected function stream(Route $route): Observer
    {
        return (new Evacuator(function() use ($route) {
            return $this->viaStream()->request($route);
        }))
            ->onError(function (\Exception $e) {
                $this->client->logger->error($e->getMessage());
            })
            ->retries($this->client->getRetriesCount())
            ->invoke();
    }

    /**
     * @return AdapterInterface|HttpAdapter
     */
    protected function viaHttp(): HttpAdapter
    {
        return $this->client->viaHttp();
    }

    /**
     * @return AdapterInterface|StreamAdapter
     */
    protected function viaStream(): StreamAdapter
    {
        return $this->client->viaStream();
    }
}
