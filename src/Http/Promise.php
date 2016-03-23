<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 01.03.2016 18:04
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Http;

use Amp\Promise as BasePromise;

/**
 * Class Promise
 * @package Gitter\Http
 */
class Promise implements BasePromise
{
    /**
     * @var BasePromise
     */
    private $promise;

    /**
     * Promise constructor.
     * @param BasePromise $promise
     * @throws \RuntimeException
     */
    public function __construct(BasePromise $promise)
    {
        $this->promise = \Amp\pipe($promise, function(Response $response) {
            return $response->json();
        });
    }

    /**
     * @param callable $cb
     * @param null $cbData
     * @return $this
     */
    public function when(callable $cb, $cbData = null)
    {
        $this->promise->when($cb, $cbData);
        return $this;
    }

    /**
     * @param callable $cb
     * @param null $cbData
     * @return $this
     */
    public function watch(callable $cb, $cbData = null)
    {
        $this->promise->watch($cb, $cbData);
        return $this;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    public function wait()
    {
        return \Amp\wait($this->promise);
    }
}
