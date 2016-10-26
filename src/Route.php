<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter;

/**
 * Class Route
 * @package Gitter
 *
 * @method static Route get(string $route)
 * @method static Route put(string $route)
 * @method static Route post(string $route)
 * @method static Route patch(string $route)
 * @method static Route delete(string $route)
 * @method static Route options(string $route)
 * @method static Route head(string $route)
 * @method static Route connect(string $route)
 * @method static Route trace(string $route)
 */
class Route
{
    /**
     * @var string
     */
    private $route;

    /**
     * @var array
     */
    private $parameters = [];

    /**
     * @var string
     */
    private $method;

    /**
     * @var string|null
     */
    private $url;

    /**
     * @var array
     */
    private $body = [];

    /**
     * Route constructor.
     * @param string $route
     * @param string $method
     */
    public function __construct(string $route, string $method = 'GET')
    {
        $this->route($route);
        $this->method($method);
        $this->toApi();
    }

    /**
     * @param string $url
     * @return $this|Route
     */
    public function to(string $url): Route
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Route
     */
    public function toApi(): Route
    {
        return $this->to('https://api.gitter.im/v1/');
    }

    /**
     * @return Route
     */
    public function toStream(): Route
    {
        return $this->to('https://stream.gitter.im/v1/');
    }

    /**
     * @return Route
     */
    public function toSocket(): Route
    {
        return $this->to('wss://ws.gitter.im/');
    }

    /**
     * @return Route
     */
    public function toFaye(): Route
    {
        return $this->to('https://gitter.im/api/v1/');
    }

    /**
     * @return Route
     */
    public function toWebhook(): Route
    {
        return $this->to('https://webhooks.gitter.im/e/');
    }

    /**
     * @param string|null $route
     * @return string
     */
    public function route(string $route = null): string
    {
        if ($route !== null) {
            $this->route = $route;
        }

        return $this->route;
    }

    /**
     * @param string|null $method
     * @return string
     */
    public function method(string $method = null): string
    {
        if ($method !== null) {
            $this->method = strtoupper($method);
        }

        return $this->method;
    }

    /**
     * @param string $parameter
     * @param string $value
     * @return $this|Route
     */
    public function with(string $parameter, string $value): Route
    {
        $this->parameters[$parameter] = $value;

        return $this;
    }

    /**
     * @param array $parameters
     * @return $this|Route
     */
    public function withMany(array $parameters): Route
    {
        foreach ($parameters as $parameter => $value) {
            $this->with($parameter, $value);
        }

        return $this;
    }

    /**
     * @param string $field
     * @param $value
     * @return Route|$this
     */
    public function withBody(string $field, $value): Route
    {
        $this->body[$field] = $value;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getBody()
    {
        if (count($this->body)) {
            return json_encode($this->body);
        }
        return null;
    }

    /**
     * @param array $parameters
     * @return string
     * @throws \InvalidArgumentException
     */
    public function build(array $parameters = []): string
    {
        if ($this->url === null) {
            throw new \InvalidArgumentException('Can not build route string. URL does not set');
        }

        $route = $this->route;
        $query = $parameters = array_merge($this->parameters, $parameters);

        foreach ($parameters as $parameter => $value) {
            $updatedRoute = str_replace(sprintf('{%s}', $parameter), $value, $route);

            if ($updatedRoute !== $route) {
                unset($query[$parameter]);
            }

            $route = $updatedRoute;
        }

        return $this->url . $route . '?' . http_build_query($query);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return static
     */
    public static function __callStatic(string $name, array $arguments = [])
    {
        return new static($arguments[0] ?? '', $name);
    }
}