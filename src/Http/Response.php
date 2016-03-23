<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 01.03.2016 17:30
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Http;

use Amp\Artax\Response as OriginalResponse;

class Response implements \IteratorAggregate
{
    /**
     * @var OriginalResponse
     */
    private $response;

    /**
     * Response constructor.
     * @param Request $request
     * @param OriginalResponse $response
     * @throws \RuntimeException
     */
    public function __construct(Request $request, OriginalResponse $response)
    {
        $this->response = $response;

        if (!$this->isValidStatusCode()) {
            throw new \RuntimeException(
                'The server returned an invalid or unrecognized response with' .
                sprintf(' status code `%s` and `%s` body', $response->getStatus(), $this->body()) .
                sprintf(' on request: `%s`', $request->getUri()->build()) .
                sprintf(' with body `%s`', $request->getBody())
            );
        }
    }

    /**
     * @return string
     */
    public function body()
    {
        return (string)$this->response->getBody();
    }

    /**
     * @param $assoc
     * @return array|\stdClass
     * @throws \RuntimeException
     */
    public function json($assoc = false)
    {
        $result = json_decode($this->body(), $assoc);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(json_last_error_msg());
        }

        return $result;
    }

    /**
     * @return array
     * @throws \RuntimeException
     */
    public function jsonArray()
    {
        return (array)$this->json(true);
    }

    /**
     * @return \stdClass
     * @throws \RuntimeException
     */
    public function jsonObject()
    {
        $result = $this->json(false);
        return is_array($result) ? $result[0] : $result;
    }

    /**
     * @return bool
     */
    public function isValidStatusCode()
    {
        $status = $this->response->getStatus();
        return $status >= 200 && $status < 400;
    }

    /**
     * @return \Generator
     * @throws \RuntimeException
     */
    public function getIterator()
    {
        $iterator = $this->jsonArray();
        foreach ($iterator as $key => $value) {
            yield $key => $value;
        }
    }
}
