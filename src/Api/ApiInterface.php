<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Api;

use Gitter\Client;
use Gitter\Http\HttpClientInterface;

/**
 * Interface ApiInterface
 * @package Gitter\Api
 */
interface ApiInterface
{
    /**
     * ConnectionInterface constructor.
     * @param Client $client
     * @param HttpClientInterface $httpClient
     */
    public function __construct(Client $client, HttpClientInterface $httpClient);
}