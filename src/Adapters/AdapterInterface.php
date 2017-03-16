<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Gitter\Adapters;

/**
 * Interface AdapterInterface
 * @package Gitter\Adapters
 */
interface AdapterInterface
{
    /**
     * @param array $options
     * @return AdapterInterface
     */
    public function setOptions(array $options = []): AdapterInterface;
}
