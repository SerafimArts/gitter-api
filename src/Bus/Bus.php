<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 01.03.2016 20:27
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Bus;

use Gitter\Client;
use Gitter\Http\Promise;

/**
 * Interface Bus
 * @package Gitter\Bus
 */
interface Bus
{
    /**
     * Bus constructor.
     * @param Client $client
     */
    public function __construct(Client $client);

    /**
     * @param $name
     * @param array $arguments
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     */
    public function __call($name, array $arguments = []);
}
