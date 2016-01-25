<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 25.01.2016 14:07
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Io;

/**
 * Interface RequestInterface
 * @package Gitter\Io
 */
interface RequestInterface
{
    /**
     * RequestInterface constructor.
     * @param string $method
     * @param string $url
     * @param array $args
     */
    public function __construct(string $method, string $url, array $args = []);

    /**
     * @param string|array $content
     * @return $this|Request|RequestInterface
     */
    public function withBody($content) : RequestInterface;

    /**
     * @param string $token
     * @return $this|Request|RequestInterface
     */
    public function withToken(string $token) : RequestInterface;

    /**
     * @param bool $state
     * @return $this|Request|RequestInterface
     */
    public function asStream($state = false) : RequestInterface;

    /**
     * @return string
     */
    public function getMethod() : string;

    /**
     * @return Url
     */
    public function getUrl() : Url;

    /**
     * @return string
     */
    public function getBody() : string;

    /**
     * @return string
     */
    public function getToken() : string;

    /**
     * @return boolean
     */
    public function isStream() : bool;
}
