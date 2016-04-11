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

/**
 * Class Client
 * @package Gitter
 *
 * @property-read HttpBus|Bus $http
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
    private $http;

    /**
     * Client constructor.
     * @param string $token
     */
    public function __construct(string $token)
    {
        $this->token = $token;
        $this->http = new HttpBus($this);
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
     * @return Bus|HttpBus
     * @throws \LogicException
     */
    public function __get($field)
    {
        if ($field === 'http') {
            return $this->http;
        }

        throw new \LogicException('Field ' . $field . ' not found in ' . static::class);
    }
}
