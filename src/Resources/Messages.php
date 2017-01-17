<?php declare(strict_types=1);
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Resources;

use Gitter\Route;

/**
 * Messages represent individual chat messages sent to a room. They are a sub-resource of a room.
 *
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
 *
 * @package Gitter\Resources
 */
class Messages extends AbstractResource
{
    /**
     * List of messages in a room in historical reversed order
     *
     * @param string $roomId Room id
     * @param string|null $query Optional search query
     * @param string $beforeId
     * @return \Generator
     */
    public function all(string $roomId, string $query = null, string $beforeId = null): \Generator
    {
        $limit    = 100;

        do {
            $route = Route::get('rooms/{roomId}/chatMessages')
                ->with('roomId', $roomId)
                ->with('limit', (string)$limit);

            if ($beforeId !== null) {
                $route->with('beforeId', (string)$beforeId);
            }

            if ($query !== null) {
                $route->with('q', $query);
            }

            $response = array_reverse($this->fetch($route));

            foreach ($response as $message) {
                $beforeId = (string)$message['id'];
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
     */
    public function find(string $roomId, string $messageId): array
    {
        return $this->fetch(
            Route::get('rooms/{roomId}/chatMessages/{messageId}')
                ->withMany([ 'roomId' => $roomId, 'messageId' => $messageId ])
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
     * Update a message
     *
     * @param string $roomId Room id
     * @param string $messageId Message id
     * @param string $content New message body
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function update(string $roomId, string $messageId, string $content): array
    {
        return $this->fetch(
            Route::put('rooms/{roomId}/chatMessages/{messageId}')
                ->withMany([ 'roomId' => $roomId, 'messageId' => $messageId ])
                ->withBody('text', $content)
        );
    }

    /**
     * Delete a message
     *
     * @param string $roomId
     * @param string $messageId
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function delete(string $roomId, string $messageId): array
    {
        return $this->update($roomId, $messageId, '');
    }
}
