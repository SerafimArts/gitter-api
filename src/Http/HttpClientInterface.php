<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Http;

use Gitter\Url\Route;
use Psr\Log\LoggerInterface;

/**
 * Interface HttpClientInterface
 * @package Gitter\Http
 */
interface HttpClientInterface
{
    /**
     * @param LoggerInterface $logger
     * @return mixed
     */
    public function setLogger(LoggerInterface $logger);

    /**
     * @param string $token
     * @return HttpClientInterface
     */
    public function setAccessToken(string $token) : HttpClientInterface;

    /**
     * @param string $url
     * @param array $parameters
     * @return Route
     */
    public function route(string $url, array $parameters = []) : Route;

    /**
     * @param Route $route
     * @param string $method
     * @return mixed
     */
    public function request(Route $route, $method = 'GET', array $body = []);
}