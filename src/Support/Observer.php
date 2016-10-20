<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

/**
 * Class Observer
 * @package Gitter\Support
 */
class Observer
{
    /**
     * @var array|\Closure[]
     */
    private $subscribers = [];

    /**
     * @param \Closure $resolver
     */
    private function resolve(\Closure $resolver)
    {
        ($resolver)(function(...$args) {
            foreach ($this->subscribers as $subscriber) {
                $subscriber(...$args);
            }
        });
    }

    /**
     * @param \Closure $closure
     * @return Observer
     */
    public function subscribe(\Closure $closure): Observer
    {
        $this->subscribers[] = $closure;

        return $this;
    }
}