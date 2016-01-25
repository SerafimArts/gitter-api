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
use React\EventLoop\LoopInterface;
use React\Promise\Deferred;
use React\Promise\PromiseInterface;

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
     * Client constructor.
     * @param LoopInterface $loop
     * @param string $token
     */
    public function __construct(LoopInterface $loop, string $token)
    {
        $this->token   = $token;
        $this->loop    = $loop;
        $this->request = Transport::http($loop, function(Request $request) use ($token) {
            return $request
                ->withDomain(static::GITTER_HTTP_API_DOMAIN)
                ->withToken($token);
        });
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
        $deferred = new Deferred();

        try {
            $response
                ->chunk(function($chunk) use ($deferred) {
                    $deferred->notify($chunk);
                })
                ->json(function($data) use ($deferred, $resolver) {
                    if (!property_exists($data, 'error')) {
                        $deferred->resolve(call_user_func($resolver, $data));
                    } else {
                        $deferred->reject(new \RuntimeException($data->error));
                    }
                })
                ->error(function(\Throwable $e) use ($deferred) {
                    $deferred->reject($e);
                });

        } catch (\Throwable $e) {

            $deferred->reject($e);
        }

        return $deferred->promise();
    }
}
