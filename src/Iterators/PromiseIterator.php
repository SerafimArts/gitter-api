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

use Gitter\Promise\Promise;
use Gitter\Promise\PromiseInterface;
use Gitter\Iterators\PromiseIterator\Controls;

/**
 * Class PromiseIterator
 * @package Gitter\Iterators
 */
class PromiseIterator extends Promise
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
     * PromiseIterator constructor.
     * @param \Closure $next
     */
    public function __construct(\Closure $next)
    {
        parent::__construct();
        $this->nextClosure = $next;
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
            $this->reject($e);
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
                                    $this->resolve($this);
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
                        $this->reject($e);
                    }
                });
        } else {
            $this->resolve();
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
