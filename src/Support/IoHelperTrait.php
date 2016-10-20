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
     * @param array $data
     * @return Request
     * @throws \InvalidArgumentException
     */
    protected function prepareRequest(string $token, Route $route, array $data = [])
    {
        $headers = $this->prepareHeaders($token);
        $body    = $this->bodyToString($route, $data);

        return new Request($route->getMethod(), $route->build(), $headers, $body, '1.1');
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

    /**
     * @param Route $route
     * @param array $body
     * @return string
     * @throws \InvalidArgumentException
     */
    private function bodyToString(Route $route, array $body = []): string
    {
        $content = count($body) ? json_encode($body) : '';

        if ($content && $route->getMethod() === 'GET') {
            throw new \InvalidArgumentException('GET requests can not contain a body');
        }

        return $content;
    }
}