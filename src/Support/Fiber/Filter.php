<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support\Fiber;

/**
 * Class Filter
 * @package Gitter\Support\Fiber
 */
class Filter
{
    /**
     * @var \Closure
     */
    private $matcher;

    /**
     * Filter constructor.
     * @param \Closure $matcher
     */
    public function __construct(\Closure $matcher)
    {
        $this->matcher = $matcher;
    }

    /**
     * @param array $item
     * @return bool
     */
    public function match(array $item) : bool
    {
        return ($this->matcher)($item);
    }
}