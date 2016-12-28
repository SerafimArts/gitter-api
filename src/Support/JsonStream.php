<?php declare(strict_types=1);
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
     * @var int
     */
    private $chunkSize = 1;

    /**
     * @var Loggable
     */
    private $logger;

    /**
     * JsonStream constructor.
     * @param int $bufferSize
     */
    public function __construct(int $bufferSize = self::BUFFER_MAX_SIZE)
    {
        $this->bufferSize = $bufferSize;
    }

    /**
     * @param Loggable $logger
     */
    public function setLogger(Loggable $logger)
    {
        $this->logger = $logger;
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
     * @param string $data
     * @param \Closure|null $callback
     * @return mixed|null
     */
    public function push(string $data, \Closure $callback = null)
    {
        // Buffer are empty and input starts with "[" or "{"
        $canBeBuffered = $this->buffer === '' && in_array($data[0], ['[', '{'], true);

        // Data can be starts buffering
        if ($canBeBuffered) {
            $this->endsWith = $data[0] === '[' ? ']' : '}';
        }

        // Add chunks for non empty buffer
        if ($canBeBuffered || $this->buffer !== '') {
            $this->buffer .= $data;

            // Try to compile
            $trimmed = rtrim($data);
            if ($trimmed[strlen($trimmed) - 1] === $this->endsWith) {
                $object = json_decode($this->buffer, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $this->buffer = '';

                    if ($callback !== null) {
                        $callback($object);
                    }

                    return $object;
                }
            }
        }

        return null;
    }

    /**
     * @param StreamInterface $stream
     * @return \Generator
     * @throws \OutOfBoundsException
     * @throws \RuntimeException
     */
    public function stream(StreamInterface $stream): \Generator
    {
        while (!$stream->eof()) {
            $data = $stream->read($this->chunkSize);

            $output = $this->push($data);
            if ($output !== null) {
                yield $output;
            }

            $this->checkSize();
        }
    }
}
