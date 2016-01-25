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
     * @return User
     * @TODO
     */
    public static function current(Client $client)
    {
        $response = $client->createRequest()->get('user');
        return new User($client, $response->getJson());
    }

    /**
     * @return \Generator|Room[]
     * @TODO
     */
    public function getRooms() : \Generator
    {
        $response = $this->client
            ->createRequest()
            ->get('user/{id}/rooms', ['id' => $this->id]);

        foreach ($response as $item) {
            yield new Room($this->client, $item);
        }
    }

    /**
     * @return \Generator|Room[]
     * @TODO
     */
    public function getOrganizations() : \Generator
    {
        $response = $this->client
            ->createRequest()
            ->get('user/{id}/orgs', ['id' => $this->id]);

        foreach ($response as $item) {
            yield new Room($this->client, $item);
        }
    }

    /**
     * @return Room|null
     * @TODO
     */
    public function getPersonalRoom()
    {
        return $this->client->getRoomByUri($this->username);
    }

    /**
     * @param $text
     * @return Message
     * @TODO
     */
    public function sendMessage($text) : Message
    {
        $room = $this->getPersonalRoom();
        return $room->sendMessage($text);
    }
}
