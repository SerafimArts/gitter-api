<?php
/**
 * This file is part of GitterApi package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Resources;

/**
 * @TODO Not implemented yet
 *
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
}
