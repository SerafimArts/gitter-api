<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 25.01.2016 22:34
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Iterators\PromiseIterator;

/**
 * Class Controls
 * @package Gitter\Iterators\PromiseIterator
 */
class Controls
{
    /**
     * @var \Closure
     */
    protected $callback;

    /**
     * @var int
     */
    protected $current = 0;

    /**
     * Controls constructor.
     * @param $current
     * @param \Closure $callback
     */
    public function __construct($current, \Closure $callback)
    {
        $this->callback = $callback;
        $this->current = $current;
    }

    /**
     * @return int
     */
    public function index()
    {
        return $this->current;
    }

    /**
     * @return mixed
     */
    public function __invoke()
    {
        return $this->next();
    }

    /**
     * @return mixed
     */
    public function next()
    {
        $closure = $this->callback;
        return $closure();
    }
}
