<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 01.03.2016 13:43
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter;

use Gitter\Bus\Bus;
use Gitter\Bus\HttpBus;
use Gitter\Http\Promise;
use Gitter\Http\Request;
use Illuminate\Support\Str;
use Amp\Artax\Client as Artax;

/**
 * Class Client
 * @package Gitter
 *
 * @property-read HttpBus|Bus $http
 *
 * @method Promise get(string $url, array $args = [], $body = null)
 * @method Promise post(string $url, array $args = [], $body = null)
 * @method Promise put(string $url, array $args = [], $body = null)
 * @method Promise delete(string $url, array $args = [], $body = null)
 *
 */
class Client
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var Artax
     */
    private $artax;

    /**
     * @var HttpBus
     */
    private $http;

    /**
     * Client constructor.
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
        $this->artax = new Artax;
        $this->http = new HttpBus($this);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return Artax
     */
    public function getArtaxClient()
    {
        return $this->artax;
    }

    /**
     * @param $name
     * @param array $arguments
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     */
    public function __call($name, array $arguments = [])
    {
        return $this->request(Str::upper($name), ...$arguments);
    }

    /**
     * @param $method
     * @param $url
     * @param array $args
     * @param null $body
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     */
    public function request($method, $url, array $args = [], $body = null)
    {
        return $this->artaxRequest($url, $args)->wrap($method, $body);
    }

    /**
     * @param $url
     * @param array $args
     * @return Request
     */
    public function artaxRequest($url, array $args = [])
    {
        return (new Request($this))
            ->to($url, $args);
    }

    /**
     * @param $field
     * @return Bus|HttpBus
     * @throws \LogicException
     */
    public function __get($field)
    {
        if ($field === 'http') {
            return $this->http;
        }

        throw new \LogicException('Field ' . $field . ' not found in ' . static::class);
    }
}
