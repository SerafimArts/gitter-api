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
class PromiseIterator
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
        $this->nextClosure = $next;
    }

    /**
     * @param \Closure $callback
     */
    public function fetch(\Closure $callback)
    {
        $closure = $this->nextClosure;

        $promise = $closure($this->current++, $this);

        if ($promise instanceof PromiseInterface) {
            $promise
                ->then(function ($data) use ($callback) {
                    if ($data instanceof \Iterator) {
                        foreach ($data as $index => $item) {
                            $next = false;

                            $callback($item, new Controls($this->index++, function () use ($data, &$next) {
                                $next = true;
                            }));

                            if (!$next) { break; }
                        }

                        $this->fetch($callback);

                    } else {
                        $callback($data, new Controls($this->index++, function () use ($callback) {
                            $this->fetch($callback);
                        }));
                    }
                });
        }
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
