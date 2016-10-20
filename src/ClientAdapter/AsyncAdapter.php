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
use Gitter\Support\Loggable;
use Gitter\Client as Gitter;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Promise\Promise;
use Gitter\Support\IoHelperTrait;
use Gitter\Support\IoLoggableTrait;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use function GuzzleHttp\json_decode as json;

/**
 * Class AsyncAdapter
 * @package Gitter\ClientAdapter
 */
class AsyncAdapter implements AdapterInterface, Loggable
{
    use IoLoggableTrait,
        IoHelperTrait;

    /**
     * @var Gitter
     */
    protected $gitter;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * ReactStreamAdapter constructor.
     * @param Gitter $gitter
     */
    public function __construct(Gitter $gitter)
    {
        $this->gitter = $gitter;
        $this->client = new Client();

        $this->setOptions([
            RequestOptions::VERIFY      => false,
            RequestOptions::SYNCHRONOUS => true,
            RequestOptions::PROGRESS    => function (...$args) {
                $this->logProgress(...$args);
            },
        ]);
    }

    /**
     * @param array $options
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @param Route $route
     * @param array $body
     * @return PromiseInterface
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function request(Route $route, array $body = [])
    {
        $request = $this->prepareRequest($this->gitter->token(), $route, $body);
        $this->logRequest($request);

        $responsePromise = $this->client->sendAsync($request, $this->options);

        $promise = new Promise(function ($unwrap = true) use ($responsePromise) {
            $responsePromise->wait($unwrap);
        });

        $responsePromise->then(function (ResponseInterface $response) use ($promise) {
            $this->logResponse($response);

            $promise->resolve(json((string)$response->getBody(), true));
        });

        return $promise;
    }

    /**
     * @param string $message
     * @param int $level
     * @return Loggable
     */
    final public function log(string $message, int $level = Logger::INFO): Loggable
    {
        $this->gitter->log($message, $level);

        return $this;
    }
}