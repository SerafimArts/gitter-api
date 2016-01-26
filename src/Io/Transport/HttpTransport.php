<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 25.01.2016 14:10
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Io\Transport;


use Gitter\Io\Request;
use React\HttpClient\Response;
use React\EventLoop\LoopInterface;
use Gitter\Io\Support\StringBuffer;
use Gitter\Io\Response as GitterResponse;
use React\HttpClient\Factory as HttpClient;
use React\Dns\Resolver\Factory as DnsResolver;
use React\SocketClient\ConnectionException;


/**
 * Class HttpTransport
 * @package Gitter\Io\Transport
 */
class HttpTransport implements TransportInterface
{
    const DEFAULT_DNS_SERVER = '8.8.8.8';

    /**
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @var \React\Dns\Resolver\Resolver
     */
    protected $dnsResolver;

    /**
     * HttpTransport constructor.
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->dnsResolver = (new DnsResolver())->createCached(static::DEFAULT_DNS_SERVER, $loop);
    }

    /**
     * @param Request $request
     * @return GitterResponse
     */
    public function send(Request $request) : GitterResponse
    {
        $client     = (new HttpClient())->create($this->loop, $this->dnsResolver);
        $headers    = $this->makeHeaders($request);
        $stream     = new GitterResponse();
        $body       = $request->getBody();
        $url        = $request->getUrl()->build();

        $connection = $client->request($request->getMethod(), $url, $headers, '1.1');

        $connection->on('response', function (Response $response) use ($stream) {
            if ($response->getCode() >= 400) {
                return $stream->reject(
                    new ConnectionException(
                        'External server return status code ' . $response->getCode(),
                        $response->getCode()
                    )
                );
            }

            $response->on('error', function (\Throwable $e) use ($stream) {
                $stream->reject($e);
            });

            $response->on('data', function ($data, Response $response) use ($stream) {
                $stream->update((string)$data);
            });
        });

        $connection->on('error', function (\Throwable $e) use ($stream) {
            $stream->reject($e);
        });

        $connection->on('end', function () use ($stream) {
            $stream->resolve($stream);
        });

        $connection->end($body ?: null);

        return $stream;
    }

    /**
     * @param Request $request
     * @return array
     */
    protected function makeHeaders(Request $request)
    {
        $headers = [
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
            'Cache-Control' => 'no-cache',
            'Authorization' => sprintf('Bearer %s', $request->getToken()),
        ];

        // If request is stream use keep-alive connection
        if ($request->isStream()) {
            $headers['Connection'] = 'Keep-Alive';
        }

        // If request has body - add content-length header
        if ($body = $request->getBody()) {
            $headers['Content-Length'] = strlen($body);
        }

        return $headers;
    }
}
