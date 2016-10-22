<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

use Gitter\Route;
use Monolog\Logger;
use React\Promise\PromiseInterface;
use Gitter\ClientAdapter\SyncBuzzAdapter;
use Gitter\ClientAdapter\AsyncBuzzAdapter;
use Gitter\ClientAdapter\StreamBuzzAdapter;

/**
 * Class BuzzAdaptersTest
 * @package Gitter\Tests
 */
class BuzzAdaptersTest extends TestCase
{
    /**
     * SYNC
     */
    public function testSyncAdapter()
    {
        $response = $this->client()->adapters->through(SyncBuzzAdapter::class)
            ->request(Route::get('user'));

        $this->assertInternalType('array', $response);
    }

    /**
     * ASYNC
     */
    public function testAsyncAdapter()
    {
        $client = $this->client();

        /** @var PromiseInterface $promise */
        $promise = $client->adapters->through(AsyncBuzzAdapter::class)
            ->request(Route::get('user'));

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $promise
            ->then(function($response) use ($client, $promise) {
                $client->disconnect();
                $this->assertTrue(is_array($response));
            }, function(\Throwable $e) {
                $this->throwException($e);
            });

        // Throws exception after 10 seconds timeout
        $client->loop->addTimer(10, function() use ($client) {
            $client->disconnect();
            $this->throwException(new \RuntimeException('Client timeout'));
        });

        $client->connect();
    }

    /**
     * STREAM
     */
    public function testStreamAdapter()
    {
        $client  = $this->client();

        $message = (string)memory_get_usage(true);

        $routeStream = Route::get('rooms/{roomId}/chatMessages')
            ->with('roomId', $this->debugRoomId())
            ->toStream();

        $routeAnswer = Route::post('rooms/{roomId}/chatMessages')
            ->with('roomId', $this->debugRoomId())
            ;


        // Connect to client
        $client->adapters->through(StreamBuzzAdapter::class)->request($routeStream)

            // Message incoming! Assert and shutting down
            ->subscribe(function($answer) use ($client, $message) {

                $this->assertInternalType('array', $answer);
                $this->assertArrayHasKey('text', $answer);

                if ($message === $answer['text']) {
                    $client->disconnect();
                }
            });


        // Send message after 1 second
        $client->loop->addTimer(1, function() use ($message, $routeAnswer) {
            $this->client()->adapters->through(SyncBuzzAdapter::class)
                ->request($routeAnswer->withBody('text', $message));
        });

        // Throws exception after 10 seconds timeout
        $client->loop->addTimer(10, function() use ($client) {
            $client->disconnect();
            $this->throwException(new \RuntimeException('Client timeout'));
        });

        $client->connect();
    }


}