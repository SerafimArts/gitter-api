<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 25.01.2016 13:50
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Io;

/**
 * Class Url
 * @package Gitter\Io
 */
class Url
{
    /**
     * @var string|null
     */
    protected $domain = null;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $args = [];

    /**
     * Url constructor.
     * @param string $url
     */
    public function __construct(string $url)
    {
        $this->url = $url;
    }

    /**
     * @param string $domain
     * @return $this|Url
     */
    public function withDomain(string $domain) : Url
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this|Url
     */
    public function with($key, $value) : Url
    {
        $this->args[$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function build() : string
    {
        $url = ($this->domain === null)
            ? $this->url
            : sprintf('%s/%s', $this->domain, $this->url);

        foreach ($this->args as $key => $value) {
            $key = sprintf('{%s}', $key);
            $url = str_replace($key, $value, $url);
        }

        return $url . '?' . http_build_query($this->args);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->build();
    }
}
