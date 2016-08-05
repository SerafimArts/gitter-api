<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Url;

/**
 * Class Route
 * @package Gitter\Url
 */
class Route
{
    /**
     * @var string
     */
    private $url;

    /**
     * Query parameters
     * @var array
     */
    private $parameters = [];

    /**
     * Route constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @param string $name
     * @param int|float|string $value
     * @return $this|Route
     */
    public function with(string $name, $value) : Route
    {
        if ($value !== null) {
            $this->parameters[(string)$name] = (string)$value;
        }

        return $this;
    }

    /**
     * @param array $parameters
     * @return Route
     */
    public function withMany(array $parameters) : Route
    {
        foreach ($parameters as $name => $value) {
            $this->with($name, $value);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function make() : string
    {
        $url = $this->url;
        foreach ($this->parameters as $name => $value) {
            $url = str_replace(sprintf('{%s}', $name), $value, $url);
        }

        return $url . '?' . http_build_query($this->parameters);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->make();
    }
}