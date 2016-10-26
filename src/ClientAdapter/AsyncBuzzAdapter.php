<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Gitter\Route;
use React\Promise\Promise;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class AsyncBuzzAdapter
 * @package Gitter\ClientAdapter
 */
class AsyncBuzzAdapter extends AbstractBuzzAdapter implements AsyncAdapterInterface
{
    /**
     * @param Route $route
     * @return Promise|PromiseInterface
     * @throws \InvalidArgumentException
     */
    public function request(Route $route)
    {
        $deferred = new Deferred();

        $request = $this->prepareRequest($this->gitter->token, $route);
        $this->logRequest($request);

        /** @var Promise $answer */
        $answer = $this->client->send($request);

        $answer->then(function(ResponseInterface $response) use ($deferred) {
            $this->logResponse($response);

            $data = json_decode((string)$response->getBody(), true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $deferred->resolve($data);
            } else {
                $deferred->reject(new \InvalidArgumentException(
                    json_last_error_msg() . ': ' . $response->getBody()
                ));
            }

        }, function(\Throwable $throwable) use ($deferred) {
            $deferred->reject($throwable);
        });

        return $deferred->promise();
    }
}