<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 01.03.2016 17:12
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Http;

/**
 * Class Uri
 * @package Gitter\Http
 */
class Uri
{
    const HOST = 'https://api.gitter.im/{version}/';

    /**
     * @param string|Uri $uri
     * @return static
     */
    public static function new($uri)
    {
        if (is_string($uri)) {
            $uri = new static($uri);
        }

        return $uri;
    }

    /**
     * @var string
     */
    private $host;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $args = [];

    /**
     * Uri constructor.
     * @param string $url
     * @param string $host
     */
    public function __construct($url = '', $host = null)
    {
        $this->url = $url;
        $this->host = $host ?? static::HOST;

        $this->addArgument('version', 'v1');
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this|Uri
     */
    public function addArgument(string $key, string $value) : Uri
    {
        $this->args[$key] = $value;
        return $this;
    }

    /**
     * @param array $args
     * @return $this|Uri
     */
    public function addArguments(array $args) : Uri
    {
        foreach ($args as $key => $value) {
            $this->addArgument($key, $value);
        }
        return $this;
    }

    /**
     * @param array $args
     * @return $this|Uri
     */
    public function setArguments(array $args) : Uri
    {
        $this->args = [];
        return $this->addArguments($args);
    }

    /**
     * @param string $url
     * @return $this|Uri
     */
    public function setUrl($url) : Uri
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @param string $host
     * @return $this|Uri
     */
    public function setHost($host) : Uri
    {
        $this->host = $host;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost() : string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }

    /**
     * @return array
     */
    public function getArgs() : array
    {
        return $this->args;
    }

    /**
     * @return string
     */
    public function build() : string
    {
        $args = $this->args;

        $url = $this->host . $this->url;
        foreach ($args as $key => $value) {
            list($before, $url) = [$url, str_replace(sprintf('{%s}', $key), $value, $url)];

            if ($before !== $url || !$value) {
                unset($args[$key]);
            }
        }

        return $url . '?' . http_build_query($args);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->build();
    }
}
