<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

use Gitter\ClientAdapter\AdapterInterface;

/**
 * Class ResourceRoomTest
 * @package Gitter\Tests
 */
class ResourceRoomTest extends TestCase
{
    public function testRooms()
    {
        $client = $this->client();

        $rooms = $client->rooms->all();

        foreach ($rooms as $room) {
            $this->assertTrue(is_array($room));
        }
    }

    public function testLeaveAndJoin()
    {
        $client = $this->client();

        $response = $client->rooms->leave($this->debugRoomId());
        $this->assertTrue(is_array($response));
        $this->assertTrue($response['success']);

        $response = $client->rooms->join($this->debugRoomId());
        $this->assertTrue(is_array($response));
        $this->assertEquals($response['id'], $this->debugRoomId());
    }

    public function testUsers()
    {
        $client = $this->client();

        $users = $client->rooms->users($this->debugRoomId());

        foreach ($users as $user) {
            $this->assertTrue(is_array($user));
        }
    }
}