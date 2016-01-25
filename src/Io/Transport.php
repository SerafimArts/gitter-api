<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 25.01.2016 14:38
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Io;

use React\EventLoop\LoopInterface;
use Gitter\Io\Transport\HttpTransport;
use Gitter\Io\Transport\TransportInterface;

/**
 * Class Transport
 * @package Gitter\Io
 */
class Transport
{
    /**
     * @param LoopInterface $loop
     * @param \Closure $requestFactory
     * @return static
     */
    public static function http(LoopInterface $loop, \Closure $requestFactory = null)
    {
        return new static(new HttpTransport($loop), $requestFactory);
    }

    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @var \Closure
     */
    protected $requestFactory;

    /**
     * Transport constructor.
     * @param TransportInterface $transport
     * @param \Closure|null $requestFactory
     */
    public function __construct(TransportInterface $transport, \Closure $requestFactory = null)
    {
        $this->transport        = $transport;
        $this->requestFactory   = ($requestFactory !== null)
            ? $requestFactory
            : function(Request $request) { return $request; };
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $args
     * @return Request
     */
    public function request(string $method, string $url, array $args = [])
    {
        return call_user_func($this->requestFactory, new Request($method, $url, $args));
    }

    /**
     * @param Request $request
     * @return Response|ResponseInterface
     */
    public function send(Request $request)
    {
        return $this->transport->send($request);
    }

    /**
     * @param string $url
     * @param array $args
     * @param array|string|null $body
     * @return Response|ResponseInterface
     */
    public function get(string $url, array $args = [], $body = null)
    {
        return $this->send($this->request('get', $url, $args)->withBody($body));
    }

    /**
     * @param string $url
     * @param array $args
     * @param array|string|null $body
     * @return Response|ResponseInterface
     */
    public function post(string $url, array $args = [], $body = null)
    {
        return $this->send($this->request('post', $url, $args)->withBody($body));
    }

    /**
     * @param string $url
     * @param array $args
     * @param array|string|null $body
     * @return Response|ResponseInterface
     */
    public function put(string $url, array $args = [], $body = null)
    {
        return $this->send($this->request('put', $url, $args)->withBody($body));
    }

    /**
     * @param string $url
     * @param array $args
     * @param array|string|null $body
     * @return Response|ResponseInterface
     */
    public function patch(string $url, array $args = [], $body = null)
    {
        return $this->send($this->request('patch', $url, $args)->withBody($body));
    }

    /**
     * @param string $url
     * @param array $args
     * @param array|string|null $body
     * @return Response|ResponseInterface
     */
    public function delete(string $url, array $args = [], $body = null)
    {
        return $this->send($this->request('delete', $url, $args)->withBody($body));
    }

    /**
     * @param string $url
     * @param array $args
     * @param array|string|null $body
     * @return Response|ResponseInterface
     */
    public function head(string $url, array $args = [], $body = null)
    {
        return $this->send($this->request('head', $url, $args)->withBody($body));
    }
}
