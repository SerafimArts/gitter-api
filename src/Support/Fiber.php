<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

use Gitter\Support\Fiber\Filter;
use Illuminate\Support\Arr;

/**
 * Class Fiber
 * @package Gitter\Support
 */
class Fiber implements \IteratorAggregate
{
    /**
     * @var array|\Generator|\Traversable
     */
    private $iterator;

    /**
     * @var array|Filter[]
     */
    private $filters = [];

    /**
     * LazyCollection constructor.
     * @param \Generator|\Traversable|array $iterator
     */
    public function __construct($iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * @param string $key
     * @param string $op
     * @param mixed $value
     * @return $this|Fiber
     */
    private function addWhereFilter(string $key, string $op, $value) : Fiber
    {
        $this->filter(new Filter(function(array $item) use ($key, $op, $value) {
            if (!array_key_exists($key, $item)) {
                return false;
            }

            $original = Arr::get($item, $key);

            switch ($op) {
                case '>':
                    return $original > $value;
                case '>=':
                    return $original >= $value;
                case '<':
                    return $original <= $value;
                case '<=':
                    return $original <= $value;

                case '=':
                case '==':
                case '===':
                    return $original === $value;

                case '<>':
                case '!=':
                case '!==':
                    return $original !== $value;

                case 'like':
                    return preg_match('/' . $value . '/isu', $original);
            }

            return false;
        }));

        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this|Fiber
     */
    public function like(string $key, $value) : Fiber
    {
        return $this->addWhereFilter($key, 'like', $value);
    }

    /**
     * @param string $key
     * @param string|mixed $operatorOrValue
     * @param null|mixed $value
     * @return $this|Fiber
     */
    public function where(string $key, $operatorOrValue, $value = null) : Fiber
    {
        if ($value === null) {
            list($value, $operatorOrValue) = [$operatorOrValue, '='];
        }

        return $this->addWhereFilter($key, $operatorOrValue, $value);
    }

    /**
     * @param string $key
     * @return $this|Fiber
     */
    public function whereNull(string $key) : Fiber
    {
        return $this->addWhereFilter($key, '=', null);
    }

    /**
     * @param string $key
     * @return $this|Fiber
     */
    public function whereNotNull(string $key) : Fiber
    {
        return $this->addWhereFilter($key, '!=', null);
    }

    /**
     * @param Filter $filter
     * @return $this|Fiber
     */
    public function filter(Filter $filter) : Fiber
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * @return \Generator
     */
    public function getIterator() : \Generator
    {
        foreach ($this->iterator as $i => $item) {
            foreach ($this->filters as $filter) {
                $match = $filter->match($item);

                if (!$match) {
                    continue(2);
                }
            }

            yield $i => $item;
        }
    }
}