<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Gitter\Route;
use Gitter\Client as Gitter;
use Gitter\Support\JsonStream;
use GuzzleHttp\RequestOptions;

/**
 * Class StreamGuzzleAdapter
 * @package Gitter\ClientAdapter
 *
 * @deprecated This is BLOCKING guzzle stream
 */
class StreamGuzzleAdapter extends GuzzleAdapter
{
    /**
     * StreamGuzzleAdapter constructor.
     * @param Gitter $gitter
     */
    public function __construct(Gitter $gitter)
    {
        parent::__construct($gitter);

        $this
            ->setOption(RequestOptions::SYNCHRONOUS, false)
            ->setOption(RequestOptions::STREAM, true);
    }

    /**
     * @param Route $route
     * @param array $body
     * @return \Generator
     * @throws \RuntimeException
     * @throws \OutOfBoundsException
     * @throws \InvalidArgumentException
     */
    public function request(Route $route, array $body = []): \Generator
    {
        $json = new JsonStream();

        $request = $this->prepareRequest($this->gitter->token(), $route, $body);
        $this->logRequest($request, 'Blocking stream');

        yield from $json->stream($this->client->send($request, $this->options)->getBody());
    }
}