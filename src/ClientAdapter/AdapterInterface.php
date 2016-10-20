<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Gitter\Route;
use Gitter\Client;

/**
 * Interface AdapterInterface
 * @package Gitter\ClientAdapter
 */
interface AdapterInterface
{
    /**
     * AdapterInterface constructor.
     * @param Client $client
     */
    public function __construct(Client $client);

    /**
     * @param Route $route
     * @param array $body
     * @return mixed
     */
    public function request(Route $route, array $body = []);
}