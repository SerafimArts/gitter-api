<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Gitter\Route;
use GuzzleHttp\Promise\Promise;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\json_decode as json;

/**
 * Class AsyncGuzzleAdapter
 * @package Gitter\ClientAdapter
 * @deprecated Guzzle adapters can be removed in future versions
 */
class AsyncGuzzleAdapter extends AbstractGuzzleAdapter implements AsyncAdapterInterface
{
    /**
     * @param Route $route
     * @return Promise|PromiseInterface
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function request(Route $route)
    {
        $request = $this->prepareRequest($this->gitter->token, $route);
        $this->logRequest($request);

        $responsePromise = $this->client->sendAsync($request, $this->options);

        $promise = new Promise(function ($unwrap = true) use ($responsePromise) {
            $responsePromise->wait($unwrap);
        });

        $responsePromise->then(function (ResponseInterface $response) use ($promise) {
            $this->logResponse($response);

            $promise->resolve(json((string)$response->getBody(), true));
        });

        return $promise;
    }
}
