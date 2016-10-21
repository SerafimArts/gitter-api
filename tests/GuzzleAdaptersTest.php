<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

use Gitter\Route;
use GuzzleHttp\Promise\PromiseInterface;
use Gitter\ClientAdapter\SyncGuzzleAdapter;
use Gitter\ClientAdapter\AsyncGuzzleAdapter;
use Gitter\ClientAdapter\StreamGuzzleAdapter;

/**
 * Class GuzzleAdaptersTest
 * @package Gitter\Tests
 */
class GuzzleAdaptersTest extends TestCase
{
    /**
     * SYNC
     */
    public function testSyncAdapter()
    {
        $response = $this->client()->through(SyncGuzzleAdapter::class)
            ->request(Route::get('user'));

        $this->assertInternalType('array', $response);
    }

    /**
     * ASYNC
     */
    public function testAsyncAdapter()
    {
        /** @var PromiseInterface $promise */
        $promise = $this->client()->through(AsyncGuzzleAdapter::class)
            ->request(Route::get('user'));

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $promise
            ->then(function($response) {
                $this->assertTrue(is_array($response));
            }, function(\Throwable $e) {
                $this->throwException($e);
            });

        $promise->wait();
    }

    /**
     * STREAMING
     */
    public function testStreamAdapter()
    {
        $client = $this->client();

        $routeStream = Route::get('rooms/{roomId}/chatMessages')
            ->with('roomId', $this->debugRoomId())
            ->toStream();

        /** @var \Generator $stream */
        $stream = $client->through(StreamGuzzleAdapter::class)->request($routeStream);

        $this->assertInstanceOf(\Generator::class, $stream);
    }
}