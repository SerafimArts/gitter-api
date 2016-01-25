<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 25.01.2016 15:36
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Io;

use Gitter\Io\Support\JsonBuffer;
use Gitter\Io\Support\StringBuffer;
use React\Promise\PromiseInterface;

/**
 * Class Response
 * @package Gitter\Io
 */
class Response implements PromiseInterface, ResponseInterface
{
    /**
     * @var array
     */
    protected $chunkObservers = [];

    /**
     * @var array
     */
    protected $errorObservers = [];

    /**
     * @var JsonBuffer
     */
    protected $jsonBuffer;

    /**
     * @var StringBuffer
     */
    protected $stringBuffer;

    /**
     * Response constructor.
     */
    public function __construct()
    {
        $this->jsonBuffer   = new JsonBuffer();
        $this->stringBuffer = new StringBuffer();

        $this->chunk(function($chunk) {
            $this->stringBuffer->add($chunk);
            $this->jsonBuffer->add($chunk);
        });

        $this->body(function() {
            $this->jsonBuffer->flush();
        });
    }

    /**
     * @param \Closure $callback
     * @return $this|ResponseInterface
     */
    public function chunk(\Closure $callback) : ResponseInterface
    {
        $this->chunkObservers[] = $callback;
        return $this;
    }

    /**
     * @param \Closure $callback
     * @return ResponseInterface
     */
    public function body(\Closure $callback) : ResponseInterface
    {
        $this->stringBuffer->subscribe($callback);
        return $this;
    }

    /**
     * @param \Closure $callback
     * @return ResponseInterface
     */
    public function json(\Closure $callback) : ResponseInterface
    {
        $this->jsonBuffer->subscribe($callback);
        return $this;
    }

    /**
     * @param \Closure $callback
     * @return $this|ResponseInterface
     */
    public function error(\Closure $callback) : ResponseInterface
    {
        $this->errorObservers[] = $callback;
        return $this;
    }

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onProgress
     * @throws \LogicException
     * @return $this
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        $this->body($onFulfilled);
        $this->error($onRejected);

        if ($onProgress !== null) {
            throw new \LogicException('Argument 3 has no effect');
        }

        return $this;
    }

    /**
     * @param \Throwable $exception
     * @return $this
     */
    public function reject(\Throwable $exception)
    {
        foreach ($this->errorObservers as $error) {
            $error($exception);
        }
        return $this;
    }

    /**
     * @param $chunk
     * @return $this
     */
    public function update($chunk)
    {
        try {
            foreach ($this->chunkObservers as $observer) {
                $observer($chunk);
            }
        } catch (\Throwable $exception) {
            $this->reject($exception);
        }

        return $this;
    }

    /**
     * @param $data
     * @return $this
     */
    public function resolve($data)
    {
        try {
            $this->stringBuffer->flush();

        } catch (\Throwable $exception) {
            $this->reject($exception);
        }

        return $this;
    }
}
