<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\ClientAdapter;

use Monolog\Logger;
use Gitter\Support\Loggable;
use Clue\React\Buzz\Browser;
use Gitter\Client as Gitter;
use Gitter\Support\IoHelperTrait;
use Gitter\Support\IoLoggableTrait;

/**
 * Class AbstractBuzzAdapter
 * @package Gitter\ClientAdapter
 */
abstract class AbstractBuzzAdapter implements AdapterInterface, Loggable
{
    use IoLoggableTrait,
        IoHelperTrait;

    /**
     * @var Gitter
     */
    protected $gitter;

    /**
     * @var Browser
     */
    protected $client;

    /**
     * StreamBuzzAdapter constructor.
     * @param Gitter $gitter
     */
    public function __construct(Gitter $gitter)
    {
        $this->gitter = $gitter;
        $this->client = new Browser($this->gitter->loop);
    }

    /**
     * @param string $message
     * @param int $level
     * @return Loggable|$this
     */
    final public function log(string $message, int $level = Logger::INFO): Loggable
    {
        $this->gitter->log($message, $level);

        return $this;
    }
}