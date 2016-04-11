<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 01.03.2016 13:43
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter;

use Gitter\Bus\Bus;
use Gitter\Bus\HttpBus;
use Gitter\Bus\StreamBus;

/**
 * Class Client
 * @package Gitter
 *
 * @property-read HttpBus|Bus $http
 * @property-read StreamBus|Bus $stream
 */
class Client
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var HttpBus
     */
    private $http = null;

    /**
     * @var StreamBus
     */
    private $stream = null;

    /**
     * Client constructor.
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $field
     * @return Bus|HttpBus|StreamBus
     * @throws \LogicException
     */
    public function __get($field)
    {
        switch ($field) {
            case 'http':
                if ($this->http === null) {
                    $this->http = new HttpBus($this);
                }
                return $this->http;

            case 'stream':
                if ($this->stream === null) {
                    $this->stream = new StreamBus($this);
                }
                return $this->stream;
        }

        throw new \LogicException('Field ' . $field . ' not found in ' . static::class);
    }
}
