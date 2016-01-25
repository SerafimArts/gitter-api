<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 25.01.2016 13:58
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Io;

/**
 * Class Request
 * @package Gitter\Io
 */
class Request implements RequestInterface
{
    /**
     * @var mixed|string
     */
    protected $method = '';

    /**
     * @var Url
     */
    protected $url = '';

    /**
     * @var string
     */
    protected $body = '';

    /**
     * @var string
     */
    protected $token = '';

    /**
     * @var bool
     */
    protected $stream = false;

    /**
     * Request constructor.
     * @param string $method
     * @param string $url
     * @param array $args
     */
    public function __construct(string $method, string $url, array $args = [])
    {
        $this->method   = mb_strtoupper($method);

        $this->url      = new Url($url);
        foreach ($args as $key => $value) {
            $this->url->with($key, $value);
        }
    }

    /**
     * @param string $domain
     * @return $this|Request
     */
    public function withDomain(string $domain) : Request
    {
        $this->url->withDomain($domain);
        return $this;
    }

    /**
     * @param string|array $content
     * @return $this|Request|RequestInterface
     */
    public function withBody($content) : RequestInterface
    {
        if (!is_string($content)) {
            $content = json_encode($content);
        }
        $this->body = $content;

        return $this;
    }

    /**
     * @param string $token
     * @return $this|Request|RequestInterface
     */
    public function withToken(string $token) : RequestInterface
    {
        $this->token = $token;
        $this->url->with('access_token', $token);

        return $this;
    }

    /**
     * @param bool $state
     * @return $this|Request|RequestInterface
     */
    public function asStream($state = false) : RequestInterface
    {
        $this->stream = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * @return Url
     */
    public function getUrl() : Url
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getBody() : string
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getToken() : string
    {
        return $this->token;
    }

    /**
     * @return boolean
     */
    public function isStream() : bool
    {
        return $this->stream;
    }
}
