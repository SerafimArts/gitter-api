<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 22.01.2016 23:59
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Io\Support;

/**
 * Class StringBuffer
 * @package Gitter\Io\Support
 */
class StringBuffer
{
    /**
     * @var string
     */
    protected $data = '';

    /**
     * @var array
     */
    protected $callbacks = [];

    /**
     * @param $chunk
     * @return StringBuffer
     */
    public function add($chunk): StringBuffer
    {
        $this->data .= $chunk;
        return $this;
    }

    /**
     * @param callable|array $callback
     * @return StringBuffer
     */
    public function subscribe($callback): StringBuffer
    {
        $this->callbacks[] = $callback;
        return $this;
    }

    /**
     * @return $this
     */
    public function clear()
    {
        $this->data = '';
        return $this;
    }

    /**
     * @return mixed|string
     */
    public function flush(): string
    {
        $result = $this->data;
        $this->data = '';

        foreach ($this->callbacks as $callback) {
            $callback($result);
        }

        return $result;
    }

    /**
     * @return mixed|integer
     */
    public function size(): integer
    {
        return strlen($this->data);
    }
}
