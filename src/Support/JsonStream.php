<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Gitter\Support;

use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

/**
 * Class JsonStream
 * @package Gitter\Support
 */
class JsonStream
{
    /**
     * @var string[]
     */
    const JSON_STARTS_WITH = ['{', '['];

    /**
     * @var int
     */
    const BUFFER_MAX_SIZE = 2 ** 14;

    /**
     * @var string
     */
    private $bufferSize;

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
     * @var LoggerInterface
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
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @throws \OutOfBoundsException
     */
    private function checkSize()
    {
        if ($this->bufferSize > 0 && \strlen($this->buffer) > $this->bufferSize) {
            throw new \OutOfBoundsException(
                sprintf('Memory leak detected. Buffer size out of %s bytes', $this->bufferSize)
            );
        }
    }

    /**
     * @param string $data
     * @return bool
     */
    private function shouldStarts(string $data): bool
    {
        return $this->buffer === '' && \in_array($data[0], self::JSON_STARTS_WITH, true);;
    }

    /**
     * @param string $data
     * @param \Closure|null $callback
     * @return mixed|null
     */
    public function push(string $data, \Closure $callback = null)
    {
        // Buffer are empty and input starts with "[" or "{"
        $canBeBuffered = $this->shouldStarts($data);

        // Data can be starts buffering
        if ($canBeBuffered) {
            $this->endsWith = $data[0] === '[' ? ']' : '}';
        }

        // Add chunks for non empty buffer
        if ($canBeBuffered || $this->buffer !== '') {
            $this->buffer .= $data;

            if ($this->isEnds($data)) {
                $object = \json_decode($this->buffer, true);

                if (\json_last_error() === JSON_ERROR_NONE) {
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
     * @param string $text
     * @return bool
     */
    private function isEnds(string $text): bool
    {
        $text = \trim($text);

        return $text && $text[\strlen($text) - 1] === $this->endsWith;
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
