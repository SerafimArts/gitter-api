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
 * Class JsonBuffer
 * @package Gitter\Io\Support
 */
class JsonBuffer
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
     * @return JsonBuffer
     */
    public function add($chunk): JsonBuffer
    {
        $chunks = explode("\n", $chunk);
        $count  = count($chunks);

        if ($count === 1) {
            $this->data .= $chunk;

        } else {
            for ($i = 0; $i < $count; $i++) {
                $this->data .= $chunks[$i];
                if ($i !== $count - 1) {
                    $this->flush();
                }
            }
        }

        return $this;
    }

    /**
     * @param callable|array $callback
     * @return JsonBuffer
     */
    public function subscribe($callback): JsonBuffer
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
     * @return void
     */
    public function flush()
    {
        if (trim($this->data) && trim($this->data) !== '[]') {
            $result = json_decode($this->data);

            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($this->callbacks as $callback) {
                    $callback($result);
                }
            }
        }

        $this->data = '';
    }

    /**
     * @return mixed|integer
     */
    public function size(): integer
    {
        return strlen($this->data);
    }
}
