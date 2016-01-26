<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 22.01.2016 20:27
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Models;

use Carbon\Carbon;
use Gitter\Client;

/**
 * Class Message
 * @package Gitter\Models
 *
 * @property-read string $id
 * @property-read string $text
 * @property-read string $html
 * @property-read Carbon $sent
 * @property-read Carbon $editedAt
 * @property-read User $fromUser
 * @property-read bool $unread
 * @property-read int $readBy
 * @property-read array $urls
 * @property-read array $issues
 * @property-read array $meta
 * @property-read int $v
 * @property-read Room $room
 */
class Message extends AbstractModel
{
    /**
     * Message constructor.
     * @param Client $client
     * @param Room $room
     * @param array|\StdClass $attributes
     */
    public function __construct(Client $client, Room $room, $attributes)
    {
        parent::__construct($client, $attributes);

        $this->set('room',      $room);
        $this->set('sent',      new Carbon($this->get('sent')));
        $this->set('editedAt',  new Carbon($this->get('editedAt', $this->get('sent'))));
        $this->set('fromUser',  new User($this->client, $this->get('fromUser')));
        $this->set('urls',      array_map(function($data) { return $data->url ?? $data; }, $this->get('urls', [])));
    }

    /**
     * @param $text
     * @return Message
     */
    public function update($text) : Message
    {
        $response = $this->client
            ->createRequest()
            ->put(
                'rooms/{roomId}/chatMessages/{id}',
                ['roomId' => $this->room->id, 'id' => $this->id],
                ['text' => (string)$text]
            );

        return $this->client->wrapResponse($response, function($response) {
            $updatedMessage = new Message($this->client, $this->room, $response);

            $this->set('text',      $updatedMessage->text);
            $this->set('html',      $updatedMessage->html);
            $this->set('editedAt',  $updatedMessage->editedAt);
        });
    }
}
