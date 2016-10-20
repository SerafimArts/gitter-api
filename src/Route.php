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
     * Route constructor.
     * @param string $route
     * @param string $method
     */
    public function __construct(string $route, string $method = 'GET')
    {
        $this->route = $route;
        $this->method = strtoupper($method);
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
     * @return string
     */
    public function getRoute(): string
    {
        return $this->route;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
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