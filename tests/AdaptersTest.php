<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

use Gitter\ClientAdapter\StreamBuzzAdapter;
use Gitter\Route;
use GuzzleHttp\Promise\PromiseInterface;
use Gitter\ClientAdapter\SyncGuzzleAdapter;
use Gitter\ClientAdapter\AsyncGuzzleAdapter;
use Gitter\ClientAdapter\StreamGuzzleAdapter;

/**
 * Class AdaptersTest
 * @package Gitter\Tests
 */
class AdaptersTest extends \PHPUnit_Framework_TestCase
{
    use UnitSupport;

    public function testGuzzleSyncAdapter()
    {
        $response = $this->client()->through(SyncGuzzleAdapter::class)
            ->request(Route::get('user')->toApi());

        $this->assertInternalType('array', $response);
    }

    public function testGuzzleAsyncAdapter()
    {
        /** @var PromiseInterface $promise */
        $promise = $this->client()->through(AsyncGuzzleAdapter::class)
            ->request(Route::get('user')->toApi());

        $this->assertInstanceOf(PromiseInterface::class, $promise);

        $promise
            ->then(function($response) {
                $this->assertInternalType('array', $response);
            })
            ->otherwise(function(\Throwable $e) {
                $this->throwException($e);
            });

        $promise->wait();
    }


    public function testGuzzleStreamAdapter()
    {
        $client = $this->client();

        $routeStream = Route::get('rooms/{roomId}/chatMessages')
            ->with('roomId', $this->debugRoomId())
            ->toStream();

        /** @var \Generator $stream */
        $stream = $client->through(StreamGuzzleAdapter::class)->request($routeStream);

        $this->assertInstanceOf(\Generator::class, $stream);
    }


    public function testBuzzStreamAdapter()
    {
        $client  = $this->client();

        $message = (string)memory_get_usage(true);

        $routeStream = Route::get('rooms/{roomId}/chatMessages')
            ->with('roomId', $this->debugRoomId())
            ->toStream();

        $routeAnswer = Route::post('rooms/{roomId}/chatMessages')
            ->with('roomId', $this->debugRoomId())
            ->toApi();


        // Connect to client
        $client->through(StreamBuzzAdapter::class)->request($routeStream)

            // Message incoming! Assert and shutting down
            ->subscribe(function($answer) use ($client, $message) {
                $this->assertInternalType('array', $answer);
                $this->assertArrayHasKey('text', $answer);
                $this->assertEquals($message, $answer['text']);

                $client->loop()->stop();
            });


        // Send message after 1 second
        $client->loop()->addTimer(1, function() use ($message, $routeAnswer) {
            $this->client()->through(SyncGuzzleAdapter::class)->request($routeAnswer, [
                'text' => $message
            ]);
        });

        // Throws exception after 10 seconds timeout
        $client->loop()->addTimer(10, function() use ($client) {
            $this->throwException(new \RuntimeException('Client timeout'));
            $client->loop()->stop();
        });

        $client->connect();
    }

}