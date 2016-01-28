<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 22.01.2016 16:32
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gitter;


use Gitter\Io\Request;
use Gitter\Io\Response;
use Gitter\Io\Transport;
use Gitter\Models\Room;
use Gitter\Models\User;
use Gitter\Promise\Promise;
use React\EventLoop\LoopInterface;
use Gitter\Promise\PromiseInterface;
use Gitter\Handlers\EmptyErrorHandler;
use Gitter\Handlers\ErrorHandlerInterface;

/**
 * Class Client
 * @package Gitter
 */
class Client
{
    const GITTER_HTTP_API_DOMAIN    = 'https://api.gitter.im/v1';
    const GITTER_STREAM_API_DOMAIN  = 'https://stream.gitter.im/v1';

    /**
     * @var string
     */
    protected $token;

    /**
     * @var Transport
     */
    protected $request;

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var EmptyErrorHandler
     */
    protected $errorHandler;

    /**
     * Client constructor.
     * @param LoopInterface $loop
     * @param string $token
     */
    public function __construct(LoopInterface $loop, string $token)
    {
        $this->token        = $token;
        $this->loop         = $loop;
        $this->request      = Transport::http($loop, function(Request $request) use ($token) {
            return $request
                ->withDomain(static::GITTER_HTTP_API_DOMAIN)
                ->withToken($token);
        });

        $this->errorHandler = new EmptyErrorHandler();

        // Delegate promise error handler to client error handler
        Promise::setErrorHandler(new class(function(\Throwable $e) { $this->throw($e); }) implements
            ErrorHandlerInterface {
                /**
                 * @var \Closure
                 */
                protected $callback;

                /**
                 * Anonymous constructor.
                 * @param \Closure $errorCallback
                 */
                public function __construct(\Closure $errorCallback)
                {
                    $this->callback = $errorCallback;
                }

                /**
                 * @param \Throwable $e
                 */
                public function fire(\Throwable $e) {
                    $closure = $this->callback;
                    $closure($e);
                }
            });
    }

    /**
     * @param ErrorHandlerInterface $handler
     * @return $this
     */
    public function setErrorHandler(ErrorHandlerInterface $handler)
    {
        $this->errorHandler = $handler;
        return $this;
    }

    /**
     * @param \Throwable $e
     * @return $this
     */
    public function throw(\Throwable $e)
    {
        $this->errorHandler->fire($e);
        return $this;
    }

    /**
     * @return Transport
     */
    public function createRequest() : Transport
    {
        return $this->request;
    }

    /**
     * @return PromiseInterface
     */
    public function getRooms() : PromiseInterface
    {
        return $this->wrapResponse(
            $this->request->get('rooms'),
            function($response) {
                foreach ($response as $item) {
                    yield new Room($this, $item);
                }
            }
        );

    }

    /**
     * @return PromiseInterface
     */
    public function getCurrentUser() : PromiseInterface
    {
        return User::current($this);
    }

    /**
     * @param string $roomId
     * @return PromiseInterface
     */
    public function getRoomById(string $roomId) : PromiseInterface
    {
        return $this->wrapResponse(
            $this->request->get('rooms/{id}', ['id' => $roomId]),
            function($data) { return new Room($this, $data); }
        );
    }

    /**
     * @param string $roomUri Room uri like "gitterhq/sandbox"
     * @return PromiseInterface
     */
    public function getRoomByUri(string $roomUri) : PromiseInterface
    {
        return $this->wrapResponse(
            $this->request->post('rooms', [], ['uri' => $roomUri]),
            function($data) { return new Room($this, $data); }
        );
    }

    /**
     * @param Response $response
     * @param \Closure $resolver
     * @return PromiseInterface
     */
    public function wrapResponse(Response $response, \Closure $resolver) : PromiseInterface
    {
        $promise = new Promise;

        try {
            $response
                ->json(function($data) use ($promise, $resolver) {
                    if (is_object($data) && property_exists($data, 'error')) {
                        $promise->reject(new \RuntimeException($data->error));
                    } else {
                        $promise->resolve(call_user_func($resolver, $data));
                    }
                })
                ->error(function(\Throwable $e) use ($promise) {
                    $promise->reject($e);
                });
        } catch (\Throwable $e) {
            $promise->reject($e);
        }

        return $promise;
    }
}
