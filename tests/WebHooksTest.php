<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

/**
 * Class WebHooksTest
 * @package Gitter\Tests
 */
class WebHooksTest extends TestCase
{
    use UnitSupport;

    public function testErrorNotify()
    {
        $this->client()->notify($this->debugHookId())
            ->levelError()
            ->send('ERROR');
    }

    public function testNormalNotify()
    {
        $this->client()->notify($this->debugHookId())
            ->levelInfo()
            ->send('ERROR');
    }
}