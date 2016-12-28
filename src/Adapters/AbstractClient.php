<?php declare(strict_types=1);
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Adapters;

use Gitter\Client;

/**
 * Class AbstractClient
 * @package Gitter\Adapters
 */
abstract class AbstractClient implements AdapterInterface
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @param Client $client
     * @return array
     */
    protected function buildHeaders(Client $client): array
    {
        return [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => sprintf('Bearer %s', $client->token())
        ];
    }

    /**
     * @param array $options
     * @return AdapterInterface
     */
    public function setOptions(array $options = []): AdapterInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param Client $client
     * @param string $message
     */
    protected function debugLog(Client $client, string $message)
    {
        if ($client->logger !== null) {
            $client->logger->debug($message);
        }
    }
}
