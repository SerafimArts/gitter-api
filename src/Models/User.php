<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 22.01.2016 18:20
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Models;


use Gitter\Client;
use Gitter\Promise\PromiseInterface;

/**
 * Class User
 * @package Gitter\Models
 *
 * @property-read string $id
 * @property-read string $username
 * @property-read string $displayName
 * @property-read string $url
 * @property-read string $avatarUrlSmall
 * @property-read string $avatarUrlMedium
 * @property-read int $v
 * @property-read string $gv
 *
 * @property-read Room[]\Generator $rooms
 * @property-read Room[]\Generator $organizations
 *
 */
class User extends AbstractModel
{
    /**
     * @param Client $client
     * @return PromiseInterface
     */
    public static function current(Client $client) : PromiseInterface
    {
        return $client->wrapResponse(
            $client->createRequest()->get('user'),
            function($response) use ($client) {
                if (is_array($response)) {
                    $response = $response[0];
                }
                return new User($client, $response);
            }
        );
    }

    /**
     * @return PromiseInterface
     */
    public function getRooms() : PromiseInterface
    {
        $response = $this->client
            ->createRequest()
            ->get('user/{id}/rooms', ['id' => $this->id]);

        return $this->client->wrapResponse($response, function($response) {
            foreach ($response as $item) {
                yield new Room($this->client, $item);
            }
        });
    }

    /**
     * @return PromiseInterface
     */
    public function getOrganizations() : PromiseInterface
    {
        $response = $this->client
            ->createRequest()
            ->get('user/{id}/orgs', ['id' => $this->id]);

        return $this->client->wrapResponse($response, function($response) {
            foreach ($response as $item) {
                yield new Room($this->client, $item);
            }
        });
    }

    /**
     * @return PromiseInterface
     */
    public function getPersonalRoom() : PromiseInterface
    {
        return $this->client->getRoomByUri($this->username);
    }

    /**
     * @param $text
     * @return PromiseInterface
     */
    public function sendMessage($text) : PromiseInterface
    {
        return $this->getPersonalRoom()->then(function(Room $room) use ($text) {
            $room->sendMessage($text);
        });
    }
}
