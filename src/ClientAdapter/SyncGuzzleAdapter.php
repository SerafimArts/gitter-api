<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Gitter\Route;
use Monolog\Logger;
use GuzzleHttp\Client;
use Gitter\Client as Gitter;
use Gitter\Support\Loggable;
use GuzzleHttp\RequestOptions;
use Gitter\Support\IoHelperTrait;
use Gitter\Support\IoLoggableTrait;
use function GuzzleHttp\json_decode as json;

/**
 * Class SyncGuzzleAdapter
 * @package Gitter\ClientAdapter
 */
class SyncGuzzleAdapter extends AsyncGuzzleAdapter
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
     * @param array $body
     * @return mixed
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function request(Route $route, array $body = [])
    {
        return parent::request($route, $body)->wait();
    }
}