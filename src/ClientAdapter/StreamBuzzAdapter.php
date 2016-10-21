<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Clue\React\Buzz\Browser;
use Gitter\Route;
use Gitter\Client as Gitter;
use Gitter\Support\IoHelperTrait;
use Gitter\Support\IoLoggableTrait;
use Gitter\Support\JsonStream;
use Gitter\Support\Loggable;
use Gitter\Support\Observer;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use React\Stream\ReadableStreamInterface;

class StreamBuzzAdapter implements AdapterInterface, Loggable
{
    use IoLoggableTrait,
        IoHelperTrait;

    /**
     * @var Gitter
     */
    private $gitter;

    /**
     * @var Browser
     */
    private $client;

    /**
     * StreamBuzzAdapter constructor.
     * @param Gitter $gitter
     */
    public function __construct(Gitter $gitter)
    {
        $this->gitter = $gitter;
        $this->client = new Browser($this->gitter->loop());
    }

    /**
     * @param Route $route
     * @param array $body
     * @return Observer
     * @throws \InvalidArgumentException
     */
    public function request(Route $route, array $body = [])
    {
        $observer = new Observer();
        $json     = new JsonStream();

        $request  = $this->prepareRequest($this->gitter->token(), $route, $body);
        $this->logRequest($request, 'Non blocking stream');

        $this->client->withOptions(['streaming' => true])
            ->send($request)->then(function(ResponseInterface $response) use ($json, $observer) {
                $this->logResponse($response);

                /* @var $body ReadableStreamInterface */
                $body = $response->getBody();

                $body->on('data', function($chunk) use ($json, $observer) {
                    $json->push($chunk, function($object) use ($observer) {
                        $observer->fire($object);
                    });
                });
            });

        return $observer;
    }

    /**
     * @param string $message
     * @param int $level
     * @return Loggable|$this
     */
    public function log(string $message, int $level = Logger::INFO): Loggable
    {
        $this->gitter->log($message, $level);
        return $this;
    }
}