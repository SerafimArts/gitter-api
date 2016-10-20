<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

use Psr\Http\Message\StreamInterface;

/**
 * Class JsonStream
 * @package Gitter\Support
 */
class JsonStream
{
    /**
     * @var int
     */
    const BUFFER_MAX_SIZE = 2 ** 14;

    /**
     * @var string
     */
    private $bufferSize = 0;

    /**
     * @var string
     */
    private $endsWith = ']';

    /**
     * @var string
     */
    private $buffer = '';

    /**
     * JsonStream constructor.
     * @param int $bufferSize
     */
    public function __construct(int $bufferSize = self::BUFFER_MAX_SIZE)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * @param string $data
     * @return \Generator
     * @throws \OutOfBoundsException
     */
    public function push(string $data): \Generator
    {
        // Buffer are empty and input starts with "[" or "{"
        $canBeBuffered = $this->buffer === '' && in_array($data[0], ['[', '{'], true);

        // Data can be starts buffering
        if ($canBeBuffered) {
            $this->buffer .= $data;
            $this->endsWith = $data[0] === '[' ? ']' : '}';

        // Add chunks for non empty buffer
        } elseif ($this->buffer !== '') {
            $this->buffer .= $data;

            // Try to compile
            if ($data[strlen($data) - 1] === $this->endsWith) {
                $data = json_decode($this->buffer);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->buffer;
                    yield $data;
                }
            }
        }

        $this->checkSize();
    }

    /**
     * @throws \OutOfBoundsException
     */
    private function checkSize()
    {
        if ($this->bufferSize > 0 && strlen($this->buffer) > $this->bufferSize) {
            throw new \OutOfBoundsException(
                sprintf('Memory leak detected . Buffer size out of %s bytes', $this->bufferSize)
            );
        }
    }

    /**
     * @param StreamInterface $stream
     * @param int $chunkSize
     * @return \Generator
     * @throws \OutOfBoundsException
     * @throws \RuntimeException
     */
    public function stream(StreamInterface $stream, int $chunkSize = 1): \Generator
    {
        while (!$stream->eof()) {
            yield from $this->push($stream->read($chunkSize));
        }
    }
}