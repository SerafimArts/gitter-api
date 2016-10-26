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
use GuzzleHttp\RequestOptions;
use function GuzzleHttp\json_decode as json;

/**
 * Class SyncGuzzleAdapter
 * @package Gitter\ClientAdapter
 * @deprecated Guzzle adapters can be removed in future versions
 */
class SyncGuzzleAdapter extends AsyncGuzzleAdapter implements SyncAdapterInterface
{
    /**
     * SyncAdapter constructor.
     * @param Gitter $gitter
     */
    public function __construct(Gitter $gitter)
    {
        parent::__construct($gitter);

        $this->setOption(RequestOptions::SYNCHRONOUS, true);
    }

    /**
     * @param Route $route
     * @return mixed
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function request(Route $route)
    {
        return parent::request($route)->wait();
    }
}
