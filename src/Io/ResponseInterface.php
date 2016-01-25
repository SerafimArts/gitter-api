<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 25.01.2016 16:06
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Io;

use Gitter\Io\Support\JsonBuffer;

/**
 * Interface ResponseInterface
 * @package Gitter\Io
 */
interface ResponseInterface
{
    /**
     * @param \Closure $callback
     * @return ResponseInterface
     */
    public function chunk(\Closure $callback) : ResponseInterface;

    /**
     * @param \Closure $callback
     * @return ResponseInterface
     */
    public function json(\Closure $callback) : ResponseInterface;

    /**
     * @param \Closure $callback
     * @return ResponseInterface
     */
    public function error(\Closure $callback) : ResponseInterface;

    /**
     * @param \Closure $callback
     * @return ResponseInterface
     */
    public function body(\Closure $callback) : ResponseInterface;
}
