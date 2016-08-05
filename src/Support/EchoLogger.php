<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

use Psr\Log\AbstractLogger;

/**
 * Class EchoLogger
 * @package Gitter\Support
 */
class EchoLogger extends AbstractLogger
{
    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = [])
    {
        echo '[' . $level . '] ' . $message . "\n";
    }
}