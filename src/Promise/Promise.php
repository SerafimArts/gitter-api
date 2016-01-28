<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 28.01.2016 14:19
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Promise;

use Gitter\Handlers\EmptyErrorHandler;
use Gitter\Handlers\ErrorHandlerInterface;

/**
 * Class Promise
 * @package Gitter\Promise
 */
class Promise implements PromiseInterface
{
    /**
     * @var array
     */
    protected $subscribers = [];

    /**
     * @var ErrorHandlerInterface
     */
    protected static $errorHandler = null;

    /**
     * @param ErrorHandlerInterface $handler
     * @return mixed
     */
    public static function setErrorHandler(ErrorHandlerInterface $handler)
    {
        static::$errorHandler = $handler;

        return static::class;
    }

    /**
     * Promise constructor.
     */
    public function __construct()
    {
        if (static::$errorHandler === null) {
            static::$errorHandler = new EmptyErrorHandler();
        }
    }

    /**
     * @param \Closure $resolve
     * @param \Closure|null $reject
     * @return $this
     */
    public function then(\Closure $resolve, \Closure $reject = null)
    {
        $this->subscribers[] = (object)[
            'resolve' => $resolve,
            'reject'  => $reject
        ];

        return $this;
    }

    /**
     * @param array ...$values
     */
    public function resolve(...$values)
    {
        foreach ($this->subscribers as $subscriber) {
            $closure = $subscriber->resolve;
            try {
                $closure(...$values);
            } catch (\Throwable $e) {
                $this->reject($e);
            }
        }
    }

    /**
     * @param \Throwable|string $exception
     * @return $this
     */
    public function reject($exception)
    {
        if (!($exception instanceof \Throwable)) {
            $exception = new \RuntimeException((string)$exception);
        }


        $hasRejects = false;

        foreach ($this->subscribers as $subscriber) {
            $closure = $subscriber->reject;
            if ($closure) {
                $hasRejects = true;
                $closure($exception);
            }
        }

        if (!$hasRejects) {
            static::$errorHandler->fire($exception);
        }

        return $this;
    }
}
