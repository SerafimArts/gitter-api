<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 11.04.2016 14:08
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Http;

/**
 * Class JsonBuffer
 * @package Gitter\Http
 */
class JsonBuffer
{
    /**
     * @var array|\Closure[]
     */
    private $events = [];

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @param \Closure $callback
     * @return $this|JsonBuffer
     */
    public function subscribe(\Closure $callback) : JsonBuffer
    {
        $this->events[] = $callback;
        return $this;
    }

    /**
     * @param string $data
     * @return $this|JsonBuffer
     * @throws \LogicException
     */
    public function push(string $data) : JsonBuffer
    {
        $this->buffer .= $data;
        $this->check();

        return $this;
    }

    /**
     * @return $this
     * @throws \LogicException
     */
    private function check()
    {
        $data = trim($this->buffer);

        if (strlen($data) > 0 && !in_array($data[0], ['{', '['], true)) {
            throw new \LogicException('Buffer data can not be a valid json in the future.');
        }

        $result = json_decode($data);

        if (json_last_error() === JSON_ERROR_NONE) {
            $this->clear();
            foreach ($this->events as $callback) {
                $callback($result);
            }
        }

        return $this;
    }

    /**
     * @return JsonBuffer
     */
    public function clear() : JsonBuffer
    {
        $this->buffer = '';
        return $this;
    }
}
