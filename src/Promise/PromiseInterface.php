<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 28.01.2016 14:19
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Promise;

use Gitter\Handlers\EmptyErrorHandler;
use Gitter\Handlers\ErrorHandlerInterface;

/**
 * Class PromiseInterface
 * @package Gitter\Promise
 */
interface PromiseInterface
{
    /**
     * @param \Closure $resolve
     * @param \Closure|null $reject
     */
    public function then(\Closure $resolve, \Closure $reject = null);
}
