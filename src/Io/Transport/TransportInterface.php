<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 25.01.2016 14:28
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Io\Transport;

use Gitter\Io\Request;
use Gitter\Io\Response;
use React\EventLoop\LoopInterface;
use React\Promise\PromiseInterface;

/**
 * Interface TransportInterface
 * @package Gitter\Io\Transport
 */
interface TransportInterface
{
    /**
     * TransportInterface constructor.
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop);

    /**
     * @param Request $request
     * @return Response
     */
    public function send(Request $request) : Response;
}
