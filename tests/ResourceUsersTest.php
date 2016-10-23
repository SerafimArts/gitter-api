<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

use Clue\React\Buzz\Message\ResponseException;
use Gitter\ClientAdapter\AdapterInterface;

/**
 * Class ResourceMessagesTest
 * @package Gitter\Tests
 */
class ResourceUsersTest extends TestCase
{
    public function testCurrentUser()
    {
        $this->assertInternalType('array', $this->client()->users->current());
    }

    public function testCurrentUserRooms()
    {
        try {
            $this->assertInternalType('array', $this->client()->users->rooms());
        } catch (ResponseException $e) {
            $this->assertContains('404', $e->getMessage());
        }
    }

    public function testCurrentUserUnreadItems()
    {
        try {
            $this->assertInternalType('array', $this->client()->users->unreadItems($this->debugRoomId()));
        } catch (ResponseException $e) {
            $this->assertContains('404', $e->getMessage());
        }
    }

    public function testCurrentUserOrgs()
    {
        try {
            $this->assertInternalType('array', $this->client()->users->orgs());
        } catch (ResponseException $e) {
            $this->assertContains('404', $e->getMessage());
        }
    }

    public function testCurrentUserRepos()
    {
        try {
            $this->assertInternalType('array', $this->client()->users->repos());
        } catch (ResponseException $e) {
            $this->assertContains('404', $e->getMessage());
        }
    }

    public function testCurrentUserChannels()
    {
        try {
            $this->assertInternalType('array', $this->client()->users->channels());
        } catch (ResponseException $e) {
            $this->assertContains('404', $e->getMessage());
        }
    }
}