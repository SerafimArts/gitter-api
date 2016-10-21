<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

use Gitter\Support\Loggable;
use React\EventLoop\LoopInterface;

/**
 * Class ClientTest
 * @package Gitter\Tests
 */
class ClientTest extends TestCase
{

    public function testTokenAccessible()
    {
        $this->assertEquals($this->client()->token(), $this->token(), 'Tokens are not equals');
    }

    public function testLoopAvailable()
    {
        $this->assertInstanceOf(LoopInterface::class, $this->client()->loop(), 'Loop are not an instance of LoopInterface');
    }

    public function testLoggerAvailable()
    {
        $this->assertInstanceOf(Loggable::class, $this->client());
    }

    public function testLoopBootable()
    {
        $booted = false;

        $loop = $this->client()->loop();

        $loop->addTimer(0, function() use (&$booted) {
            $booted = true;
        });

        $loop->run();
        $this->assertTrue($booted);
    }
}