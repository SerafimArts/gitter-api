<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 01.03.2016 19:06
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Bus;

use Amp\Artax\Client as Artax;
use Gitter\Client;
use Gitter\Http\Promise;
use Gitter\Http\Request;
use Gitter\Support\RequestIterator;
use Illuminate\Support\Str;

/**
 * Class HttpBus
 * @package Gitter\Bus
 *
 * @since 1.0 https://developer.gitter.im/docs/rest-api
 *
 * @method Promise get(string $url, array $args = [], $body = null)
 * @method Promise post(string $url, array $args = [], $body = null)
 * @method Promise put(string $url, array $args = [], $body = null)
 * @method Promise delete(string $url, array $args = [], $body = null)
 *
 */
class HttpBus implements Bus
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var Artax
     */
    private $artax;

    /**
     * Bus constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->artax = new Artax;
        $this->client = $client;
    }

    /**
     * List rooms the current user is in
     *
     * All the parameters are optional:
     *   - q: Search query
     *
     * @param array $args
     * @return Promise
     */
    public function getRooms(array $args = []) : Promise
    {
        if (array_key_exists('q', $args)) {
            $args['q'] = (string)$args['q'];
        }

        return $this->get('rooms', $args);
    }

    /**
     * List of Users currently in the room
     *
     * All the parameters are optional:
     *   - q: Search query
     *   - skip: Skip n users.
     *   - limit: maximum number of users to return (default 30).
     *
     * @param string $roomId Room id
     * @param array $args
     * @return Promise
     */
    public function getRoomUsers(string $roomId, array $args = [])
    {
        if (array_key_exists('q', $args)) {
            $args['q'] = (string)$args['q'];
        }

        if (array_key_exists('skip', $args)) {
            $args['skip'] = (int)$args['skip'];
        }

        if (array_key_exists('limit', $args)) {
            $args['limit'] = (int)$args['limit'];
        }

        return $this->get('rooms/{roomId}/users', array_merge(['roomId' => $roomId], $args));
    }

    /**
     * @param string $roomId
     * @return RequestIterator
     * @throws \Exception
     */
    public function getRoomUsersIterator(string $roomId)
    {
        return new RequestIterator(function($page) use ($roomId) {
            return $this->getRoomUsers($roomId, ['limit' => 30, 'skip' => 30 * $page])->wait();
        });
    }

    /**
     * List of Gitter channels (rooms) nested under the specified room
     *
     * @param string $roomId Room id
     * @return Promise
     */
    public function getRoomChannels(string $roomId)
    {
        return $this->get('rooms/{roomId}/channels', ['roomId' => $roomId]);
    }

    /**
     * Alias
     *
     * @param string $uri Room uri
     * @return Promise
     */
    public function getRoomByUri(string $uri)
    {
        return $this->joinRoom($uri);
    }

    /**
     * To join a room you'll need to provide a URI for it.
     * Said URI can represent a GitHub Org, a GitHub Repo or a Gitter Channel.
     *  - If the room exists and the user has enough permission to access it, it'll be added to the room.
     *  - If the room doesn't exist but the supplied URI represents a GitHub Org or GitHub Repo the user
     * is an admin of, the room will be created automatically and the user added.
     *
     * @param string $uri Required URI of the room you would like to join
     * @return Promise
     */
    public function joinRoom(string $uri)
    {
        return $this->post('rooms', [], ['uri' => $uri]);
    }

    /**
     * Get room by id
     * @param string $roomId Room id
     * @return Promise
     */
    public function getRoomById(string $roomId)
    {
        return $this->get('rooms/{roomId}', ['roomId' => $roomId]);
    }

    /**
     * Remove a user from a room. This can be self-inflicted to leave the the room and remove room from your left menu.
     *
     * @param string $roomId
     * @param string $userId
     * @return Promise
     */
    public function removeUserFromRoom(string $roomId, string $userId)
    {
        return $this->delete('rooms/{roomId}/users/{userId}', [], [
            'roomId' => $roomId,
            'userId' => $userId,
        ]);
    }

    /**
     * Update room information.
     * Parameters:
     *  - topic: Room topic.
     *  - noindex: Whether the room is indexed by search engines
     *  - tags: Tags that define the room.
     *
     * @param string $roomId Room id
     * @param array $args
     * @return Promise
     */
    public function updateRoomInfo(string $roomId, array $args = [])
    {
        if (array_key_exists('topic', $args)) {
            $args['topic'] = (string)$args['topic'];
        }

        if (array_key_exists('noindex', $args)) {
            $args['noindex'] = (bool)$args['noindex'];
        }

        if (array_key_exists('tags', $args)) {
            $args['tags'] = implode(', ', (array)$args['tags']);
        }

        return $this->put('rooms/{roomId}', ['roomId' => $roomId], $args);
    }

    /**
     * Delete room
     *
     * @param string $roomId Room id
     * @return Promise
     */
    public function deleteRoom(string $roomId)
    {
        return $this->delete('rooms/{roomId}', [
            'roomId' => $roomId,
        ]);
    }

    /**
     * List of messages in a room
     *
     * All the parameters are optional:
     *   - skip: Skip n messages
     *   - beforeId: Get messages before beforeId
     *   - afterId: Get messages after afterId
     *   - aroundId: Get messages around aroundId including this message
     *   - limit: Maximum number of messages to return
     *   - q: Search query
     *
     * @param string $roomId Room id
     * @param array $args
     * @return Promise
     */
    public function getMessages(string $roomId, array $args = [])
    {
        return $this->get('rooms/{roomId}/chatMessages', array_merge([
            'roomId' => $roomId,
        ], $args));
    }

    /**
     * @param string $roomId
     * @param int $chain
     * @return RequestIterator
     */
    public function getMessagesIterator(string $roomId, int $chain = 100)
    {
        $lastMessageId  = null;

        return new RequestIterator(function($page) use ($roomId, $chain, &$lastMessageId) {
            $query = ['limit' => $chain];

            if ($lastMessageId !== null) {
                $query['beforeId'] = $lastMessageId;
            }

            $result = $this->getMessages($roomId, $query)->wait();

            if (count($result) > 0) {
                $lastMessageId = $result[0]->id;
            }

            return $result;
        });
    }

    /**
     * There is also a way to retrieve a single message using its id.
     *
     * @param string $roomId Room id
     * @param string $messageId Message id
     * @return Promise
     */
    public function getMessage(string $roomId, string $messageId)
    {
        return $this->get('rooms/{roomId}/chatMessages/{messageId}', [
            'roomId'    => $roomId,
            'messageId' => $messageId,
        ]);
    }

    /**
     * Send a message to a room.
     *
     * @param string $roomId Room id
     * @param string $text Message text
     * @return Promise
     */
    public function sendMessage(string $roomId, string $text)
    {
        return $this->post('rooms/{roomId}/chatMessages', ['roomId' => $roomId], [
            'text' => $text,
        ]);
    }

    /**
     * Update a message.
     *
     * @param string $roomId Room id
     * @param string $messageId Message id
     * @param string $text Required Body of the message.
     * @return Promise
     */
    public function updateMessage(string $roomId, string $messageId, string $text)
    {
        return $this->put('rooms/{roomId}/chatMessages/{messageId}', [
            'roomId'    => $roomId,
            'messageId' => $messageId,
        ], [
            'text' => $text,
        ]);
    }

    /**
     * Get the current user.
     *
     * @return Promise
     */
    public function getCurrentUser()
    {
        return $this->get('user');
    }

    /**
     * Get user by id.
     *
     * @param string $userId User id
     * @return Promise
     */
    public function getUser(string $userId)
    {
        return $this->get('user/{userId}', [
            'userId' => $userId,
        ]);
    }

    /**
     * List of Rooms the user is part of.
     *
     * @param string $userId User id
     * @return Promise
     */
    public function getUserRooms(string $userId)
    {
        return $this->get('user/{userId}/rooms', [
            'userId' => $userId,
        ]);
    }

    /**
     * @param string $userId
     * @param string $roomId
     * @return Promise
     */
    public function getUserUnreadItems(string $userId, string $roomId)
    {
        return $this->get('user/{userId}/rooms/{roomId}/unreadItems', [
            'userId' => $userId,
            'roomId' => $roomId,
        ]);
    }

    /**
     * There is an additional endpoint nested under rooms that you can use to mark chat messages as read.
     *
     * Parameters:
     *   - chat: Array of chatIds.
     *
     * @param string $userId User id
     * @param string $roomId Room id
     * @param array $args
     * @return Promise
     */
    public function readItems(string $userId, string $roomId, array $args = [])
    {
        $args['chat'] = array_key_exists('chat', $args) ? (array)$args['chat'] : [];

        return $this->post('user/{userId}/rooms/{roomId}/unreadItems', [
            'userId' => $userId,
            'roomId' => $roomId,
        ]);
    }

    /**
     * List of the user's GitHub Organisations and their respective Room if available.
     *
     * @param string $userId User id
     * @return Promise
     */
    public function getUserOrgs(string $userId)
    {
        return $this->get('user/{userId}/orgs', ['userId' => $userId]);
    }

    /**
     * List of the user's GitHub Repositories and their respective Room if available.
     * Note: It'll return private repositories if the current user has granted Gitter privileges to access them.
     *
     * @param string $userId User id
     * @return Promise
     */
    public function getUserRepos(string $userId)
    {
        return $this->get('user/{userId}/repos', ['userId' => $userId]);
    }

    /**
     * List of Gitter channels nested under the current user.
     *
     * @param string $userId User id
     * @return Promise
     */
    public function getUserChannels(string $userId)
    {
        return $this->get('user/{userId}/channels', ['userId' => $userId]);
    }

    /**
     * @param $name
     * @param array $arguments
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function __call($name, array $arguments = [])
    {
        $method = Str::upper($name);

        if (!in_array($method, ['GET', 'POST', 'PUT', 'DELETE'], true)) {
            throw new \InvalidArgumentException('Unavailable method ' . $method);
        }

        return $this->request($method, ...$arguments);
    }

    /**
     * @param $method
     * @param $url
     * @param array $args
     * @param null $body
     * @return Promise|\Amp\Promise
     * @throws \RuntimeException
     */
    public function request($method, $url, array $args = [], $body = null)
    {
        return $this->artax($url, $args)->wrap($method, $body);
    }

    /**
     * @param $url
     * @param array $args
     * @return Request
     */
    private function artax($url, array $args = [])
    {
        return (new Request($this->client, $this->artax))->to($url, $args);
    }
}
