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
use Gitter\Support\IoHelperTrait;
use Gitter\Support\IoLoggableTrait;
use function GuzzleHttp\json_decode as json;

/**
 * Class GuzzleAdapter
 * @package Gitter\ClientAdapter
 */
abstract class GuzzleAdapter implements AdapterInterface, Loggable
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

        $this
            ->setOption(RequestOptions::VERIFY, false)
            ->setOption(RequestOptions::PROGRESS, function (...$args) {
                $this->logProgress(...$args);
            });
    }

    /**
     * @param string $key
     * @param $value
     * @return $this|AdapterInterface
     */
    public function setOption(string $key, $value): AdapterInterface
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @param Route $route
     * @param array $body
     * @return mixed
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    abstract public function request(Route $route, array $body = []);

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