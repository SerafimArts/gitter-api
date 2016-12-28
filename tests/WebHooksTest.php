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
    /**
     * @return void
     */
    public function testErrorNotify()
    {
        $this->client()
            ->notify($this->debugHookId())
            ->error('Travis CI Unit error notification test');
    }

    /**
     * @return void
     */
    public function testNormalNotify()
    {
        $this->client()
            ->notify($this->debugHookId())
            ->info('Travis CI Unit info notification test');
    }
}
