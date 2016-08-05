<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

/**
 * Class ApiTestCase
 * @package Gitter\Tests
 */
class ApiTestCase extends \PHPUnit_Framework_TestCase
{
    use UnitSupport;

    /**
     * @rteurn void
     */
    public function testTokenAccessible()
    {
        $this->assertEquals($this->getClient()->getToken(), $this->getToken());
    }
}