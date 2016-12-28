<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

use Gitter\Resources\Rooms;
use GuzzleHttp\Exception\ClientException;
use React\Promise\Timer\TimeoutException;

/**
 * Class RoomsTest
 * @package Gitter\Tests
 */
class RoomsTest extends TestCase
{
    /**
     * @var Rooms
     */
    private $rooms;

    /**
     * @return void
     */
    public function setUp()
    {
        $this->rooms = $this->client()->rooms;

        $this->assertInternalType('array', $this->rooms->join($this->debugRoomId()));
    }

    /**
     * @return void
     */
    public function testAllAction()
    {
        $this->assertInternalType('array', $this->rooms->all());
    }

    /**
     * @return void
     */
    public function testLeaveAndJoinAction()
    {
        $this->assertInternalType('array', $this->rooms->leave($this->debugRoomId()));

        $this->assertInternalType('array', $this->rooms->join($this->debugRoomId()));
    }

    /**
     * @return void
     */
    public function testFindByNameAction()
    {
        $this->assertInternalType('array', $this->rooms->findByName('KarmaBot/KarmaTest'));
    }

    /**
     * @return void
     */
    public function testKickAction()
    {
        $this->assertInternalType('array', $this->rooms->kick($this->debugRoomId(), $this->userId()));

        $this->assertInternalType('array', $this->rooms->join($this->debugRoomId()));
    }

    /**
     * @return void
     */
    public function testUpdateTopicAction()
    {
        $topic = md5(microtime(true) . random_int(0, PHP_INT_MAX));

        try {
            $response = $this->rooms->topic($this->debugRoomId(), $topic);

            $this->assertInternalType('array', $response);
            $this->assertArrayHasKey('topic', $response);
            $this->assertEquals($topic, $response['topic']);

        } catch (ClientException $exception) {
            $this->assertEquals(403, $exception->getCode());
        }
    }

    /**
     * @return void
     */
    public function testUpdateIndexAction()
    {
        try {
            $response = $this->rooms->searchIndex($this->debugRoomId(), true);
            $this->assertInternalType('array', $response);

            $response = $this->rooms->searchIndex($this->debugRoomId(), false);
            $this->assertInternalType('array', $response);

        } catch (ClientException $exception) {
            $this->assertEquals(403, $exception->getCode());
        }
    }

    /**
     * @return void
     */
    public function testUpdateTagsAction()
    {
        try {
            $response = $this->rooms->tags($this->debugRoomId(), ['a', 'b', 'asdasd']);
            $this->assertInternalType('array', $response);

        } catch (ClientException $exception) {
            $this->assertEquals(403, $exception->getCode());
        }
    }

    /**
     * @return void
     */
    public function testDeleteRoomAction()
    {
        // $this->rooms->delete(...)
    }

    /**
     * @return void
     */
    public function testUsersListAction()
    {
        $response = $this->rooms->users($this->debugRoomId());
        $this->assertInstanceOf(\Traversable::class, $response);
    }

    /**
     * @return void
     */
    public function testMessagesEvent()
    {
        $client = $this->client();

        $message = [];

        $client->rooms->messages($this->debugRoomId())->subscribe(function ($data) use (&$message, $client) {
            $message = $data;
            $client->loop()->stop();
        });

        $client->loop->addTimer(1, function () use ($client) {
            $client->messages->create($this->debugRoomId(), 'DEBUG');
        });

        $client->loop->addTimer(10, function () {
            throw new TimeoutException('Test execution timeout');
        });

        $client->connect();

        $this->assertEquals('DEBUG', $message['text'] ?? '');
    }
}
