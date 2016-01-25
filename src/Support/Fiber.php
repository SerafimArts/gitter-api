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

/**
 * Class Fiber
 * @package Gitter\Support
 */
class Fiber implements \IteratorAggregate
{
    /**
     * @var \Traversable
     */
    protected $iterator;

    /**
     * @var int
     */
    protected $limit = PHP_INT_MAX;

    /**
     * @var int
     */
    protected $current = 0;

    /**
     * @var \Closure
     */
    protected $fetching;

    /**
     * @var array|\Closure[]
     */
    protected $filters = [];

    /**
     * Fiber constructor.
     */
    public function __construct()
    {
        $this->fetching = function() { return []; };
    }

    /**
     * @param \Closure $filter
     * @return $this
     */
    public function filter(\Closure $filter) : Fiber
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * @param int $count
     * @return $this
     */
    public function limit(int $count) : Fiber
    {
        $this->limit = (int)$count;
        return $this;
    }

    /**
     * @param \Closure $from
     * @return $this
     */
    public function fetch(\Closure $from) : Fiber
    {
        $this->fetching = $from;
        return $this;
    }

    /**
     * @param string|\Closure $field
     * @param mixed $value
     * @param string $compare
     * @return Fiber
     */
    public function where($field, $value, string $compare = '=') : Fiber
    {
        $availableComparisons = ['=', '==', '===', '>', '>=', '<', '<=', '!=', '!==', '<>'];
        if (!in_array($compare, $availableComparisons, true)) {
            throw new \LogicException('Invalid comparison');
        }

        $fieldGetter = $field instanceof \Closure
            ? $field
            : function($item) use ($field) { return $item->$field; };

        return $this->filter(function($item) use ($fieldGetter, $value, $compare) {
            switch ($compare) {
                case '=':
                case '==':
                    return $fieldGetter($item) == $compare;
                case '===':
                    return $fieldGetter($item) === $compare;
                case '>':
                    return $fieldGetter($item) > $compare;
                case '>=':
                    return $fieldGetter($item) >= $compare;
                case '<':
                    return $fieldGetter($item) < $compare;
                case '<=':
                    return $fieldGetter($item) <= $compare;
                case '!=':
                case '<>':
                    return $fieldGetter($item) != $compare;
                case '!==':
                    return $fieldGetter($item) !== $compare;
            }
            return true;
        });
    }

    /**
     * @return \Generator
     */
    public function getIterator() : \Generator
    {
        $invokeNext = function() {
            $iterator = $this->fetching;
            yield from call_user_func($iterator, $this->current);
        };


        while ($this->current < $this->limit) {
            $before   = $this->current;
            $iterator = $invokeNext();

            foreach ($iterator as $item) {

                foreach ($this->filters as $filter) {
                    if (!call_user_func($filter, $item)) {
                        continue;
                    }
                }

                yield $this->current++ => $item;
            }

            if ($before === $this->current) {
                break;
            }
        }
    }
}
