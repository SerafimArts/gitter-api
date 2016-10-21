<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Gitter\Route;
use Clue\React\Block;

/**
 * Class SyncBuzzAdapter
 * @package Gitter\ClientAdapter
 */
class SyncBuzzAdapter extends AsyncBuzzAdapter implements SyncAdapterInterface
{
    /**
     * @param Route $route
     * @return mixed
     */
    public function request(Route $route)
    {
        return Block\await(parent::request($route), $this->gitter->loop());
    }
}