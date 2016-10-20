<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Tests;

use Gitter\Client;
use Gitter\Route;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Class UnitSupport
 * @package Gitter\Tests
 */
trait UnitSupport
{
    /**
     * @var string
     */
    protected $debugRoomId = '56019a060fc9f982beb17a5e';

    /**
     * @return string
     */
    public function token() : string
    {
        return $_ENV['token'] ?? $_SERVER['token'] ?? '';
    }
    
    /**
     * @return Client
     */
    public function client()
    {
        $logger = new Logger('phpunit');
        $logger->pushHandler(new StreamHandler(STDOUT, Logger::DEBUG));

        return new Client($this->token(), $logger);
    }
}