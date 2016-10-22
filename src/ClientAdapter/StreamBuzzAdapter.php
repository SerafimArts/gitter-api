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
use Gitter\Support\Observer;
use Gitter\Support\JsonStream;
use Psr\Http\Message\ResponseInterface;
use React\Stream\ReadableStreamInterface;

/**
 * Class StreamBuzzAdapter
 * @package Gitter\ClientAdapter
 */
class StreamBuzzAdapter extends AbstractBuzzAdapter implements StreamingAdapterInterface
{
    /**
     * @param Route $route
     * @return Observer
     * @throws \InvalidArgumentException
     */
    public function request(Route $route): Observer
    {
        $observer = new Observer();
        $json     = new JsonStream();
        $request  = $this->prepareRequest($this->gitter->token, $route);

        $this->logRequest($request, 'Non blocking stream');

        /** @var Promise $promise */
        $promise = $this->client->withOptions(['streaming' => true])->send($request);

        $promise
            ->then(function(ResponseInterface $response) use ($json, $observer) {
                $this->logResponse($response);

                /* @var $body ReadableStreamInterface */
                $body = $response->getBody();

                $body->on('data', function($chunk) use ($json, $observer) {
                    $json->push($chunk, function($object) use ($observer) {
                        $observer->fire($object);
                    });
                });
            });

        return $observer;
    }
}