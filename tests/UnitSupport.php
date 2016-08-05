<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;
use Gitter\Client;

/**
 * Class UnitSupport
 * @package Gitter\Tests
 */
trait UnitSupport
{
    /**
     * @return string
     */
    public function getToken() : string
    {
        return $_ENV['token'] ?? '';
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return new Client($this->getToken());
    }
}