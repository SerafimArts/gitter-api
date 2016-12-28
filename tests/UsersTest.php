<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

/**
 * Class UsersTest
 * @package Gitter\Tests
 */
class UsersTest extends TestCase
{
    public function testCurrentUser()
    {
        $this->assertInternalType('array', $this->client()->users->current());
        $this->assertEquals($this->userId(), $this->client()->users->current()['id'] ?? null);
    }
}
