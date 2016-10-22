<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Resources;

use Gitter\Route;

/**
 * Class Common
 * @package Gitter\Resources
 */
class Common extends AbstractResource
{
    /**
     * @param Route $route
     * @return mixed
     */
    public function to(Route $route)
    {
        return $this->fetch($route);
    }
}