<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Http;

use Gitter\Support\Fiber;
use Gitter\Url\Route;

/**
 * Class HttpClient
 * @package Gitter\Http
 */
class HttpClient extends AsyncHttpClient
{
    /**
     * @param Route $route
     * @param string $method
     * @param array $body
     * @return Fiber
     */
    public function request(Route $route, $method = 'GET', array $body = [])
    {
        //
    }
}