<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 22.01.2016 17:49
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Models;

use Gitter\Client;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;

/**
 * Class AbstractModel
 * @package Gitter\Models
 */
abstract class AbstractModel extends Fluent
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * AbstractModel constructor.
     * @param Client $client
     * @param array|\StdClass $attributes
     */
    public function __construct(Client $client, $attributes)
    {
        $this->client = $client;
        parent::__construct((array)$attributes);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        $getter = 'get' . Str::studly($key);

        if (method_exists($this, $getter)) {
            return call_user_func([$this, $getter]);
        }

        return parent::__get($key);
    }

    /**
     * @param $attribute
     * @return bool
     */
    public function has($attribute) : bool
    {
        return array_key_exists($attribute, $this->attributes);
    }

    /**
     * @param $attribute
     * @param $value
     * @return $this
     */
    protected function set($attribute, $value) : AbstractModel
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString() : string
    {
        return (string)$this->toJson();
    }

    /**
     * @return array
     */
    public function __debugInfo() : array
    {
        return $this->toArray();
    }
}
