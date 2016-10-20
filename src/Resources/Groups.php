<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Resources;

use Gitter\Client;

/**
 * Class Groups
 * @package Gitter\Resources
 */
class Groups implements ResourceInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * Groups constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }
}