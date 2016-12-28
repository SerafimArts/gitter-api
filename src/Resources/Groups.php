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
 * Group schema
 *  - id:           Group ID.
 *  - name:         Group name.
 *  - uri:          Group URI on Gitter.
 *  - backedBy:     Security descriptor. Describes the backing object we get permissions from.
 *      - type:         [null|'ONE_TO_ONE'|'GH_REPO'|'GH_ORG'|'GH_USER']
 *      - linkPath:     Represents how we find the backing object given the type
 *  - avatarUrl:    Base avatar URL (add s parameter to size)
 *
 * @package Gitter\Resources
 */
class Groups extends AbstractResource
{
    const TYPE_ONE_TO_ONE   = 'ONE_TO_ONE';
    const TYPE_GITHUB_REPO  = 'GH_REPO';
    const TYPE_GITHUB_ORG   = 'GH_ORG';
    const TYPE_GITHUB_USER  = 'GH_USER';

    /**
     * List groups the current user is in.
     * Parameters: none
     *
     * @return mixed
     */
    public function all()
    {
        return $this->fetch(Route::get('groups'));
    }

    /**
     * List of rooms nested under the specified group.
     *
     * @param string $groupId
     * @return mixed
     */
    public function rooms(string $groupId)
    {
        return $this->fetch(Route::get('groups/{groupId}/rooms')->with('groupId', $groupId));
    }
}
