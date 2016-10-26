<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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
     * @var string
     */
    private $icon = null;

    /**
     * WebHook constructor.
     * @param Client $client
     * @param string $hookId
     */
    public function __construct(Client $client, string $hookId)
    {
        parent::__construct($client);

        $this->hookId = $hookId;
    }

    /**
     * @return $this|WebHook
     */
    public function error(): WebHook
    {
        $this->level = static::HOOK_LEVEL_ERROR;

        return $this;
    }

    /**
     * @return $this|WebHook
     */
    public function info(): WebHook
    {
        $this->level = static::HOOK_LEVEL_INFO;

        return $this;
    }

    /**
     * @param string $type
     * @return $this|WebHook
     */
    public function icon(string $type): WebHook
    {
        $this->icon = $type;

        return $this;
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function send(string $message)
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