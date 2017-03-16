<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Gitter;

use Gitter\Resources\AbstractResource;

/**
 * Class WebHook
 * @package Gitter
 */
class WebHook extends AbstractResource
{
    const HOOK_LEVEL_INFO   = 'info';
    const HOOK_LEVEL_ERROR  = 'error';

    /**
     * @var string
     */
    private $hookId;

    /**
     * @var string
     */
    private $level = self::HOOK_LEVEL_INFO;

    /**
     * @var string|null
     */
    private $icon;

    /**
     * WebHook constructor.
     * @param Client $client
     * @param string $hookId
     * @throws \InvalidArgumentException
     */
    public function __construct(Client $client, string $hookId)
    {
        parent::__construct($client);

        $this->hookId = $hookId;

        if (!$this->hookId) {
            throw new \InvalidArgumentException('Invalid Hook Id');
        }
    }

    /**
     * @param string $level
     * @return WebHook
     */
    public function withLevel(string $level): WebHook
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @param string $message
     * @return array
     * @throws \Throwable
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function error(string $message): array
    {
        return $this->withLevel(static::HOOK_LEVEL_ERROR)->send($message);
    }

    /**
     * @param string $message
     * @return array
     * @throws \Throwable
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function info(string $message): array
    {
        return $this->withLevel(static::HOOK_LEVEL_INFO)->send($message);
    }

    /**
     * @param string $type
     * @return $this|WebHook
     */
    public function withIcon(string $type): WebHook
    {
        $this->icon = $type;

        return $this;
    }

    /**
     * @param string $message
     * @return array
     * @throws \Throwable
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function send(string $message): array
    {
        return $this->fetch($this->buildRoute($message));
    }

    /**
     * @param string $message
     * @return Route
     */
    private function buildRoute(string $message): Route
    {
        $icon = $this->level === static::HOOK_LEVEL_ERROR ? 'error' : $this->level;

        $route = Route::post($this->hookId)
            ->toWebhook()
            ->withBody('message', $message)
            ->withBody('errorLevel', $this->level);

        if ($this->icon !== null) {
            $route->withBody('icon', $icon);
        }

        return $route;
    }
}
