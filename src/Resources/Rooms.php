<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Gitter\Resources;

use Gitter\Route;
use Gitter\Support\Observer;

/**
 * A Room in Gitter can represent a GitHub Organisation, a GitHub Repository, a Gitter Channel
 *  or a One-to-one conversation.
 *
 * In the case of the Organisations and Repositories, the access control policies are inherited from GitHub.
 *
 * The following types of room exist:
 *  - ORG:          A room that represents a GitHub Organisation.
 *  - REPO:         A room that represents a GitHub Repository.
 *  - ONETOONE:     A one-to-one chat.
 *  - ORG_CHANNEL:  A Gitter channel nested under a GitHub Organisation.
 *  - REPO_CHANNEL: A Gitter channel nested under a GitHub Repository.
 *  - USER_CHANNEL: A Gitter channel nested under a GitHub User.
 *
 * Room schema:
 *  - id:              Room ID.
 *  - name:            Room name.
 *  - topic:           Room topic. (default: GitHub repo description)
 *  - uri:             Room URI on Gitter.
 *  - oneToOne:        Indicates if the room is a one-to-one chat.
 *  - users:           List of users in the room.
 *  - userCount:       Count of users in the room.
 *  - unreadItems:     Number of unread messages for the current user.
 *  - mentions:        Number of unread mentions for the current user.
 *  - lastAccessTime:  Last time the current user accessed the room in ISO format.
 *  - favourite:       Indicates if the room is on of your favourites.
 *  - lurk:            Indicates if the current user has disabled notifications.
 *  - url:             Path to the room on gitter.
 *  - githubType:      Type of the room.
 *  - tags:            Tags that define the room.
 *  - v:               Room version.
 *
 * @package Gitter\Resources
 */
class Rooms extends AbstractResource implements \IteratorAggregate
{
    const GITHUB_ORG   = 'ORG';
    const ORG_CHANNEL  = 'ORG_CHANNEL';

    const GITHUB_REPO  = 'REPO';
    const REPO_CHANNEL = 'REPO_CHANNEL';

    const ONE_TO_ONE   = 'ONETOONE';
    const USER_CHANNEL = 'USER_CHANNEL';

    /**
     * List rooms the current user is in
     *
     * @param string $query Search query
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function all(string $query = null): array
    {
        if ($query !== null) {
            return $this->fetch(Route::get('rooms')->with('q', $query));
        }

        return $this->fetch(Route::get('rooms'));
    }

    /**
     * To join a room you'll need to provide a URI for it.
     * Said URI can represent a GitHub Org, a GitHub Repo or a Gitter Channel.
     *  - If the room exists and the user has enough permission to access it, it'll be added to the room.
     *  - If the room doesn't exist but the supplied URI represents a GitHub Org or GitHub Repo the user
     * is an admin of, the room will be created automatically and the user added.
     *
     * @param string $roomId Required ID of the room you would like to join
     * @param string $userId Required ID of the user
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function joinUser(string $roomId, string $userId): array
    {
        return $this->fetch(
            Route::post('user/{userId}/rooms')->with('userId', $userId)
                ->withBody('id', $roomId)
        );
    }

    /**
     * Join to target room
     *
     * @param string $roomId Required ID of the room you would like to join
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function join(string $roomId): array
    {
        return $this->joinUser($roomId, $this->client()->authId());
    }

    /**
     * To join a room you'll need to provide a URI for it.
     *
     * Said URI can represent a GitHub Org, a GitHub Repo or a Gitter Channel.
     *  - If the room exists and the user has enough permission to access it, it'll be added to the room.
     *  - If the room doesn't exist but the supplied URI represents a GitHub Org or GitHub Repo the user
     *
     * is an admin of, the room will be created automatically and the user added.
     *
     * @param string $name Required URI of the room you would like to join
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function joinByName(string $name): array
    {
        return $this->fetch(Route::post('rooms')->withBody('uri', $name));
    }

    /**
     * @param string $name
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function findByName(string $name): array
    {
        return $this->joinByName($name);
    }

    /**
     * Kick target user from target room
     *
     * @param string $roomId Required ID of the room
     * @param string $userId Required ID of the user
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function kick(string $roomId, string $userId): array
    {
        return $this->fetch(
            Route::delete('rooms/{roomId}/users/{userId}')
                ->with('roomId', $roomId)
                ->with('userId', $userId)
        );
    }

    /**
     * This can be self-inflicted to leave the the room and remove room from your left menu.
     *
     * @param string $roomId Required ID of the room
     * @return array
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     * @throws \GuzzleHttp\Exception\ClientException
     */
    public function leave(string $roomId): array
    {
        return $this->kick($roomId, $this->client()->authId());
    }

    /**
     * Sets up a new topic of target room
     *
     * @param string $roomId Room id
     * @param string $topic Room topic
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function topic(string $roomId, string $topic): array
    {
        return $this->fetch(
            Route::put('rooms/{roomId}')
                ->with('roomId', $roomId)
                ->withBody('topic', $topic)
        );
    }

    /**
     * Sets the room is indexed by search engines
     *
     * @param string $roomId Room id
     * @param bool $enabled Enable or disable room indexing
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function searchIndex(string $roomId, bool $enabled = true): array
    {
        return $this->fetch(
            Route::put('rooms/{roomId}')
                ->with('roomId', $roomId)
                ->withBody('noindex', !$enabled)
        );
    }

    /**
     * Sets the tags that define the room
     *
     * @param string $roomId Room id
     * @param array $tags Target tags
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function tags(string $roomId, array $tags = []): array
    {
        return $this->fetch(
            Route::put('rooms/{roomId}')
                ->with('roomId', $roomId)
                ->withBody('tags', implode(', ', $tags))
        );
    }

    /**
     * If you hate one of the rooms - you can destroy it!
     * Fatality.
     *
     * @internal BE CAREFUL: THIS IS VERY DANGEROUS OPERATION.
     *
     * @param string $roomId Target room id
     * @return array
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function delete(string $roomId): array
    {
        return $this->fetch(
            Route::delete('rooms/{roomId}')
                ->with('roomId', $roomId)
        );
    }

    /**
     * List of Users currently in the room.
     *
     * @param string $roomId Target room id
     * @param string $query Optional query for users search
     * @return \Generator
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function users(string $roomId, string $query = null): \Generator
    {
        $skip  = 0;
        $limit = 30;

        do {
            $route = Route::get('rooms/{roomId}/users')
                ->withMany([
                    'roomId' => $roomId,
                    'skip'   => $skip,
                    'limit'  => $limit,
                ]);

            if ($query !== null) {
                $route->with('q', $query);
            }

            yield from $response = $this->fetch($route);

        } while(\count($response) >= $limit && ($skip += $limit));
    }

    /**
     * Use the streaming API to listen events.
     * The streaming API allows real-time access to messages fetching.
     *
     * @param string $roomId
     * @return Observer
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function events(string $roomId): Observer
    {
        return $this->stream(
            Route::get('rooms/{roomId}/events')
                ->with('roomId', $roomId)
                ->toStream()
        );
    }

    /**
     * Use the streaming API to listen messages.
     * The streaming API allows real-time access to messages fetching.
     *
     * @param string $roomId
     * @return Observer
     * @throws \InvalidArgumentException
     * @throws \Throwable
     */
    public function messages(string $roomId): Observer
    {
        return $this->stream(
            Route::get('rooms/{roomId}/chatMessages')
                ->with('roomId', $roomId)
                ->toStream()
        );
    }

    /**
     * @return \Generator
     * @throws \InvalidArgumentException
     * @throws \Throwable
     * @throws \Exception
     */
    public function getIterator(): \Generator
    {
        $rooms = $this->all();

        foreach ($rooms as $i => $room) {
            yield $i => $room;
        }
    }
}
