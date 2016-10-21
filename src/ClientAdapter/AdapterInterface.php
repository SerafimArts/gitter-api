<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Gitter\Route;
use Gitter\Client as Gitter;

/**
 * Interface AdapterInterface
 * @package Gitter\ClientAdapter
 */
interface AdapterInterface
{
    const TYPE_SYNC     = 'sync';
    const TYPE_ASYNC    = 'async';
    const TYPE_STREAM   = 'stream';

    /**
     * AdapterInterface constructor.
     * @param Gitter $gitter
     */
    public function __construct(Gitter $gitter);

    /**
     * @param Route $route
     * @return mixed
     */
    public function request(Route $route);
}