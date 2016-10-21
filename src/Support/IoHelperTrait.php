<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

use Gitter\Route;
use GuzzleHttp\Psr7\Request;

/**
 * Class IoHelperTrait
 * @package Gitter\Support
 */
trait IoHelperTrait
{
    /**
     * @param string $token
     * @param Route $route
     * @return Request
     * @throws \InvalidArgumentException
     */
    protected function prepareRequest(string $token, Route $route)
    {
        $headers = $this->prepareHeaders($token);

        if ($route->getBody() !== null && $route->method() === 'GET') {
            throw new \InvalidArgumentException('GET requests can not contain a body');
        }

        return new Request($route->method(), $route->build(), $headers, $route->getBody(), '1.1');
    }

    /**
     * @param string $token
     * @return array
     */
    private function prepareHeaders(string $token): array
    {
        $headers = [
            'Accept'        => 'application/json',
            'Content-Type'  => 'application/json',
            'Authorization' => sprintf('Bearer %s', $token),
        ];

        return $headers;
    }
}