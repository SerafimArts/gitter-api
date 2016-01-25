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
namespace Gitter\Support;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

/**
 * Class PromiseIterator
 * @package Gitter\Support
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
    public function next(\Closure $callback)
    {
        $closure = $this->nextClosure;

        $promise = $closure($this->current++, $this);

        if ($promise instanceof PromiseInterface) {
            $promise
                ->then(function ($data) use ($callback) {
                    $callback($data, function () use ($callback) {
                        $this->next($callback);
                    });
                });
        }
    }

    /**
     * @return $this
     */
    public function rewind()
    {
        $this->current = 0;
        return $this;
    }
}
