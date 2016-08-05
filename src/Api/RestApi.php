<?php
/**
 * This file is part of dsp-178 package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Api;

use Gitter\Client;
use Gitter\Url\Route;
use Gitter\Support\RequestIterator;
use Gitter\Http\HttpClientInterface;

/**
 * Class RestApi
 * @package Gitter\Api
 */
class RestApi implements ApiInterface
{
    /**
     * @var Client
     */
    private $gitter;

    /**
     * @var HttpClientInterface
     */
    private $client;

    /**
     * HttpConnection constructor.
     * @param Client $gitter
     * @param HttpClientInterface $client
     */
    public function __construct(Client $gitter, HttpClientInterface $client)
    {
        $this->gitter = $gitter;
        $this->client = $client;
    }

    /**
     * @param string $url
     * @param array $parameters
     * @return Route
     */
    private function route(string $url, array $parameters = []) : Route
    {
        return $this->client->route($url, $parameters);
    }

    /**
     * @param string $url
     * @param array $parameters
     * @return mixed
     */
    private function get(string $url, array $parameters = [])
    {
        return $this->client->request($this->route($url, $parameters));
    }

    /**
     * @param string $url
     * @param array $parameters
     * @param array $body
     * @return mixed
     */
    private function post(string $url, array $parameters = [], array $body = [])
    {
        return $this->client->request($this->route($url, $parameters), 'POST', $body);
    }

    /**
     * @param string $url
     * @param array $parameters
     * @param array $body
     * @return mixed
     */
    private function put(string $url, array $parameters = [], array $body = [])
    {
        return $this->client->request($this->route($url, $parameters), 'PUT', $body);
    }

    /**
     * @param string $url
     * @param array $parameters
     * @param array $body
     * @return mixed
     */
    private function delete(string $url, array $parameters = [], array $body = [])
    {
        return $this->client->request($this->route($url, $parameters), 'DELETE', $body);
    }

    /**
     * List rooms the current user is in
     *
     * @param string|null $query Search query
     * @return mixed
     */
    public function getRooms(string $query = null)
    {
        return $this->get('rooms', ['q' => $query]);
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
     * @return mixed
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
     * List of Gitter channels (rooms) nested under the specified room
     *
     * @param string $roomId Room id
     * @return mixed
     */
    public function getRoomChannels(string $roomId)
    {
        return $this->get('rooms/{roomId}/channels', ['roomId' => $roomId]);
    }

    /**
     * Alias
     *
     * @param string $uri Room uri
     * @return mixed
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
     * @return mixed
     */
    public function joinRoom(string $uri)
    {
        return $this->post('rooms', [], ['uri' => $uri]);
    }

    /**
     * Get room by id
     * @param string $roomId Room id
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
     */
    public function getMessages(string $roomId, array $args = [])
    {
        return $this->get('rooms/{roomId}/chatMessages', array_merge([
            'roomId' => $roomId,
        ], $args));
    }

    /**
     * There is also a way to retrieve a single message using its id.
     *
     * @param string $roomId Room id
     * @param string $messageId Message id
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
     */
    public function getCurrentUser()
    {
        return $this->get('user');
    }

    /**
     * Get user by id.
     *
     * @param string $userId User id
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
     */
    public function getUserRepos(string $userId)
    {
        return $this->get('user/{userId}/repos', ['userId' => $userId]);
    }

    /**
     * List of Gitter channels nested under the current user.
     *
     * @param string $userId User id
     * @return mixed
     */
    public function getUserChannels(string $userId)
    {
        return $this->get('user/{userId}/channels', ['userId' => $userId]);
    }
}