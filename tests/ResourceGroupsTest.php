<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

/**
 * Class ResourceMessagesTest
 * @package Gitter\Tests
 */
class ResourceGroupsTest extends TestCase
{
    public function testGroups()
    {
        $this->assertInternalType('array', $this->client()->groups->all());
    }

    public function testRooms()
    {
        $client = $this->client();

        $groups = $client->groups->all();
        foreach ($groups as $group) {
            $this->assertInternalType('array', $client->groups->rooms($group['id']));
            break;
        }
    }
}