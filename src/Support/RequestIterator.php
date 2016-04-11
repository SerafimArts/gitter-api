<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 23.03.2016 16:24
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

/**
 * Class RequestIterator
 * @package Gitter
 */
class RequestIterator implements \IteratorAggregate
{
    /**
     * @var \Closure
     */
    private $request;

    /**
     * @var int
     */
    private $page = 0;

    /**
     * ApiIterator constructor.
     * @param \Closure $request
     */
    public function __construct(\Closure $request)
    {
        $this->request = $request;
    }

    /**
     * @return \Generator
     */
    public function getIterator() : \Generator
    {
        do {
            $result = ($this->request)($this->page++);
            if (!count($result)) { break; }

            foreach ($result as $item) {
                yield $item;
            }
        } while(true);
    }
}
