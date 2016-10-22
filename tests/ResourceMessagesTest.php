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
 * Class ResourceMessagesTest
 * @package Gitter\Tests
 */
class ResourceMessagesTest extends TestCase
{
    public function testMessages()
    {
        $client   = $this->client();

        $messages = $client->messages->all($this->debugRoomId());

        foreach ($messages as $message) {
            break;
        }

        $this->assertNotEmpty($message);
    }
}