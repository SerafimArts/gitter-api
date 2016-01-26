<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 22.01.2016 19:25
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Iterators;

use React\Promise\PromiseInterface;
use Gitter\Iterators\PromiseIterator\Controls;

/**
 * Class PromiseIterator
 * @package Gitter\Iterators
 */
class PromiseIterator implements PromiseInterface
{
    /**
     * @var \Closure
     */
    protected $nextClosure;

    /**
     * @var int
     */
    protected $current = 0;

    /**
     * @var int
     */
    protected $index = 0;

    /**
     * @var array|\Closure[]
     */
    protected $fulfilled = [];

    /**
     * @var array|\Closure[]
     */
    protected $rejected = [];

    /**
     * @var array|\Closure[]
     */
    protected $progress = [];

    /**
     * PromiseIterator constructor.
     * @param \Closure $next
     */
    public function __construct(\Closure $next)
    {
        $this->nextClosure = $next;
    }

    /**
     * @param callable|null $onFulfilled
     * @param callable|null $onRejected
     * @param callable|null $onProgress
     * @return $this
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null, callable $onProgress = null)
    {
        if ($onFulfilled !== null) { $this->fulfilled[] = $onFulfilled; }
        if ($onRejected  !== null) { $this->rejected[]  = $onRejected;  }
        if ($onProgress  !== null) { $this->progress[]  = $onProgress;  }

        return $this;
    }

    /**
     * @param \Closure $callback
     * @return $this
     */
    public function fetch(\Closure $callback)
    {
        $closure = $this->nextClosure;
        $promise = null;

        try {
            $promise = $closure($this->current++, $this);
        } catch (\Throwable $e) {
            foreach ($this->rejected as $callback) {
                $callback($e);
            }
        }

        if ($promise instanceof PromiseInterface) {
            $promise
                ->then(function ($data) use ($callback) {
                    try {
                        if ($data instanceof \Iterator) {
                            foreach ($data as $index => $item) {
                                $next = false;

                                $callback($item, new Controls($this->index++, function () use ($data, &$next) {
                                    $next = true;
                                }));

                                foreach ($this->progress as $callback) {
                                    $callback($item, new Controls($this->index));
                                }

                                if (!$next) {
                                    return null;
                                }
                            }

                            $this->fetch($callback);

                        } else {
                            $callback($data, new Controls($this->index++, function () use ($callback) {
                                $this->fetch($callback);
                            }));

                            foreach ($this->progress as $callback) {
                                $callback($data, new Controls($this->index));
                            }
                        }
                    } catch (\Throwable $e) {
                        foreach ($this->rejected as $callback) {
                            $callback($e);
                        }
                    }
                });
        } else {
            $value = $promise;
            foreach ($this->fulfilled as $callback) {
                if ($result = $callback($value)) {
                    $value = $result;
                }
            }
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function rewind()
    {
        $this->index = $this->current = 0;
        return $this;
    }
}
