<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

use Evenement\EventEmitterTrait;

/**
 * Class JsonStream
 * @package Gitter\Support
 */
class JsonStream
{
    use EventEmitterTrait;

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * @return void
     */
    public function compile()
    {
        $data = json_decode($this->buffer);

        if (json_last_error() === JSON_ERROR_NONE) {
            $this->dispose();
            $this->emit('data', [$data]);
        }
    }

    /**
     * @param string $data
     * @return $this
     */
    public function push(string $data)
    {
        $this->buffer .= $data;
        $this->compile();

        return $this;
    }

    /**
     * @return void
     */
    public function dispose()
    {
        $this->buffer = '';
    }
}