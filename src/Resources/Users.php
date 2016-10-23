<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Resources;

use Gitter\Route;

/**
 * User schema
 *  - id:               Gitter User ID.
 *  - username:         Gitter/GitHub username.
 *  - displayName:      Gitter/GitHub user real name.
 *  - url:              Path to the user on Gitter.
 *  - avatarUrlSmall:   User avatar URI (small).
 *  - avatarUrlMedium:  User avatar URI (medium).
 *
 * @package Gitter\Resources
 */
class Users extends AbstractResource
{
    /**
     * Returns the current user logged in.
     *
     * @return array
     * @throws \InvalidArgumentException
     */
    public function current()
    {
        return $this->currentUser();
    }

    /**
     * List of Rooms the user is part of.
     *
     * @param string|null $userId User id
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function rooms(string $userId = null)
    {
        return $this->fetch(
            Route::get('user/{userId}/rooms')
                ->with('userId', $userId ?? $this->currentUserId())
        );
    }

    /**
     * You can retrieve unread items and mentions using the following endpoint.
     *
     * @param string $roomId
     * @param string|null $userId
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function unreadItems(string $roomId, string $userId = null)
    {
        return $this->fetch(
            Route::get('user/{userId}/rooms/{roomId}/unreadItems')
                ->withMany(['userId' => $userId ?? $this->currentUserId(), 'roomId' => $roomId])
        );
    }

    /**
     * There is an additional endpoint nested under rooms that you can use to mark chat messages as read
     *
     * @param string $roomId
     * @param array $messageIds
     * @param string|null $userId
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function markAsRead(string $roomId, array $messageIds, string $userId = null)
    {
        return $this->fetch(
            Route::post('user/{userId}/rooms/{roomId}/unreadItems')
                ->withMany(['userId' => $userId ?? $this->currentUserId(), 'roomId' => $roomId])
                ->withBody('chat', $messageIds)
        );
    }

    /**
     * List of the user's GitHub Organisations and their respective Room if available.
     *
     * @param string|null $userId
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function orgs(string $userId = null)
    {
        return $this->fetch(
            Route::get('user/{userId}/orgs')
                ->with('userId', $userId ?? $this->currentUserId())
        );
    }

    /**
     * List of the user's GitHub Repositories and their respective Room if available.
     *
     * Note: It'll return private repositories if the current user has granted Gitter privileges to access them.
     *
     * @param string|null $userId
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function repos(string $userId = null)
    {
        return $this->fetch(
            Route::get('user/{userId}/repos')
                ->with('userId', $userId ?? $this->currentUserId())
        );
    }

    /**
     * List of Gitter channels nested under the current user.
     *
     * @param string|null $userId
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function channels(string $userId = null)
    {
        return $this->fetch(
            Route::get('user/{userId}/channels')
                ->with('userId', $userId ?? $this->currentUserId())
        );
    }
}
