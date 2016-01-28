<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 28.01.2016 13:55
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Handlers;

/**
 * Class EmptyErrorHandler
 * @package Gitter
 */
class EmptyErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param \Throwable $e
     */
    public function fire(\Throwable $e)
    {
        // Do nothing
    }
}
