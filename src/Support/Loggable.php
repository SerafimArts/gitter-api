<?php declare(strict_types=1);
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

use Monolog\Logger;

/**
 * Interface Loggable
 * @package Gitter\Support
 */
interface Loggable
{
    /**
     * @param string $message
     * @param int $level
     * @return Loggable
     */
    public function log(string $message, int $level = Logger::INFO): Loggable;
}
