<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 28.01.2016 13:55
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Handlers;

/**
 * Class DebugErrorHandler
 * @package Gitter
 */
class DebugErrorHandler implements ErrorHandlerInterface
{
    /**
     * @param \Throwable $e
     */
    public function fire(\Throwable $e)
    {
        $error  = '';
        $error .= sprintf('%s: "%s"', get_class($e), $e->getMessage());
        $error .= "\n";
        $error .= ('  in ' . $e->getFile() . ':' . $e->getLine());
        $error .= "\n";
        $error .= '  > ' . str_replace("\n", "\n  > ", $e->getTraceAsString());
        $error .= "\n";

        echo $error;
        flush();
    }
}
