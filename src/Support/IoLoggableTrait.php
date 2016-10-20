<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Support;

use Monolog\Logger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class IoLoggableTrait
 * @package Gitter\Support
 * @mixin Loggable
 */
trait IoLoggableTrait
{
    /**
     * @param $totalLoad
     * @param $bytesLoad
     * @param $totalUpload
     * @param $bytesUpload
     */
    protected function logProgress($totalLoad, $bytesLoad, $totalUpload, $bytesUpload)
    {
        if ($totalLoad || $bytesLoad) {
            $output = '    --> ' . number_format((int)$totalLoad / 1024, 2) . 'Kib/' .
                number_format($bytesLoad / 1024, 2) . 'Kib';

            $this->log($output, Logger::DEBUG);
        }


        if ($totalUpload || $bytesUpload) {
            $input = '   <--  ' . number_format((int)$totalUpload / 1024, 2) . 'Kib/' .
                number_format($bytesUpload / 1024, 2) . 'Kib';

            $this->log($input, Logger::DEBUG);
        }
    }

    /**
     * @param ResponseInterface $response
     * @param string $type
     */
    protected function logResponse(ResponseInterface $response, $type = 'http')
    {
        $args    = [ucfirst($type), $response->getStatusCode(), $response->getReasonPhrase()];
        /** @noinspection PrintfScanfArgumentsInspection */
        $this->log(sprintf(' <== (%s) %s %s', ...$args));


        $headers = $this->removeTokenFromLogs($response->getHeaders());
        $this->log('  Response headers: ' . json_encode($headers), Logger::DEBUG);
    }

    /**
     * @param RequestInterface $request
     * @param string $type
     */
    protected function logRequest(RequestInterface $request, $type = 'http')
    {
        $args = [ucfirst($type),  $request->getMethod(), $request->getUri()];
        /** @noinspection PrintfScanfArgumentsInspection */
        $this->log(sprintf(' ==> (%s) %s %s', ...$args));


        $headers = $this->removeTokenFromLogs($request->getHeaders());
        $this->log('  Request headers: ' . json_encode($headers), Logger::DEBUG);

        $size = strlen($request->getBody());
        if ($size > 0) {
            $this->log(
                sprintf('  Request body (%sb): %s', $size, $request->getBody()),
                Logger::DEBUG
            );
        }
    }

    /**
     * @param array $headers
     * @return array
     */
    private function removeTokenFromLogs(array $headers)
    {
        if (array_key_exists('Authorization', $headers)) {
            $headers['Authorization'] = '--hidden--';
        }

        return $headers;
    }
}