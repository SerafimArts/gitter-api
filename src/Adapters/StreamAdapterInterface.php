<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Gitter\Adapters;

use Gitter\Route;
use Gitter\Support\Observer;
use React\EventLoop\LoopInterface;

/**
 * Interface StreamAdapterInterface
 * @package Gitter\Adapters
 */
interface StreamAdapterInterface extends AdapterInterface
{
    /**
     * @param Route $route
     * @return Observer
     */
    public function request(Route $route): Observer;

    /**
     * @return LoopInterface
     */
    public function getEventLoop(): LoopInterface;
}
