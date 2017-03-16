<?php
/**
 * This file is part of GitterApi package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Gitter\Resources;

use Gitter\Route;

/**
 * Messages represent individual chat messages sent to a room. They are a sub-resource of a room.
 * Message schema:
 *  - id:           ID of the message.
 *  - text:         Original message in plain-text/markdown.
 *  - html:         HTML formatted message.
 *  - sent:         ISO formatted date of the message.
 *  - editedAt:     ISO formatted date of the message if edited.
 *  - fromUser:     (User)[user-resource] that sent the message.
 *  - unread:       Boolean that indicates if the current user has read the message.
 *  - readBy:       Number of users that have read the message.
 *  - urls:         List of URLs present in the message.
 *  - mentions:     List of @Mentions in the message.
 *  - issues:       List of #Issues referenced in the message.
 *  - meta:         Metadata. This is currently not used for anything.
 *  - v:            Version.
 *  - gv:           Stands for "Gravatar version" and is used for cache busting.
 * @package Gitter\Resources
 */
class Messages extends AbstractResource
{
    /**
     * List of messages in a room in historical reversed order.
     *
     * @param string $roomId Room id
     * @param string|null $query Optional search query
     * @return \Generator
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function all(string $roomId, string $query = null): \Generator
    {
        yield from $this->allBeforeId($roomId, null, $query);
    }

    /**
     * Returns all messages before target message id.
     *
     * @param string $roomId
     * @param string|null $beforeId
     * @param string|null $query
     * @return \Generator
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Throwable
     */
    public function allBeforeId(string $roomId, string $beforeId = null, string $query = null)
    {
        $limit = 100;

        do {
            $route = $this->routeForMessagesIterator($roomId, $limit, $query);

            if ($beforeId !== null) {
                $route->with('beforeId', $beforeId);
            }

            $response = array_reverse($this->fetch($route));

            foreach ($response as $message) {
                $beforeId = (string)$message['id'];
                yield $message;
            }

        } while (count($response) >= $limit);
    }

    /**
     * @param string $roomId
     * @param int $limit
     * @param string|null $query
     * @return Route
     */
    private function routeForMessagesIterator(string $roomId, int $limit, string $query = null): Route
    {
        $route = Route::get('rooms/{roomId}/chatMessages')
            ->with('roomId', $roomId)
            ->with('limit', (string)$limit);

        if ($query !== null) {
            $route->with('q', $query);
        }

        return $route;
    }

    /**
     * Returns all messages after target message id.
     *
     * @param string $roomId
     * @param string|null $afterId
     * @param string|null $query
     * @return \Generator
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Throwable
     */
    public function allAfterId(string $roomId, string $afterId = null, string $query = null)
    {
        $limit = 100;

        do {
            $route = $this->routeForMessagesIterator($roomId, $limit, $query);

            if ($afterId !== null) {
                $route->with('afterId', $afterId);
            }

            $response = (array)$this->fetch($route);

            foreach ($response as $message) {
                $afterId = (string)$message['id'];
                yield $message;
            }
        } while (count($response) >= $limit);
    }

    /**
     * There is also a way to retrieve a single message using its id.
     *
     * @param string $roomId Room id
     * @param string $messageId Message id
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function find(string $roomId, string $messageId): array
    {
        return $this->fetch(
            Route::get('rooms/{roomId}/chatMessages/{messageId}')
                ->withMany(['roomId' => $roomId, 'messageId' => $messageId])
        );
    }

    /**
     * Send a message to a room.
     *
     * @param string $roomId Room id
     * @param string $content Message body
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function create(string $roomId, string $content): array
    {
        return $this->fetch(
            Route::post('rooms/{roomId}/chatMessages')
                ->with('roomId', $roomId)
                ->withBody('text', $content)
        );
    }

    /**
     * Delete a message.
     *
     * @param string $roomId
     * @param string $messageId
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function delete(string $roomId, string $messageId): array
    {
        return $this->update($roomId, $messageId, '');
    }

    /**
     * Update a message.
     *
     * @param string $roomId Room id
     * @param string $messageId Message id
     * @param string $content New message body
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function update(string $roomId, string $messageId, string $content): array
    {
        return $this->fetch(
            Route::put('rooms/{roomId}/chatMessages/{messageId}')
                ->withMany(['roomId' => $roomId, 'messageId' => $messageId])
                ->withBody('text', $content)
        );
    }
}
