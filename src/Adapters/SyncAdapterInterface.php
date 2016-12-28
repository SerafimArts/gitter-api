<?php declare(strict_types=1);
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Adapters;

use Gitter\Route;

/**
 * Interface SyncAdapterInterface
 * @package Gitter\Adapters
 */
interface SyncAdapterInterface extends AdapterInterface
{
    /**
     * @param Route $route
     * @return mixed
     */
    public function request(Route $route): array;
}
