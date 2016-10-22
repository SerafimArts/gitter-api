<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Resources;

use Gitter\ClientAdapter\AdapterInterface;
use Gitter\ClientAdapter\SyncAdapterInterface;
use Gitter\Route;

/**
 * @TODO Not implemented yet
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
     * @param string $roomId
     * @param string|null $query
     * @return \Generator
     * @throws \InvalidArgumentException
     */
    public function all(string $roomId, string $query = null)
    {
        $adapter  = $this->using(AdapterInterface::TYPE_SYNC);

        $beforeId = null;
        $limit    = 100;

        do {
            $route = Route::get('rooms/{roomId}/chatMessages')
                ->with('roomId', $roomId)
                ->with('limit', $limit);

            if ($beforeId !== null) {
                $route->with('beforeId', $beforeId);
            }

            $response = array_reverse($adapter->request($route));

            foreach ($response as $message) {
                $beforeId = $message['id'];
                yield $message;
            }

        } while (count($response) >= $limit);
    }
}