<?php
/**
 * This file is part of GitterAPI package.
 *
 * @author Serafim <nesk@xakep.ru>
 * @date 22.01.2016 17:49
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Gitter\Models;

use Carbon\Carbon;
use Gitter\Client;
use Gitter\Io\Transport;
use React\Promise\PromiseInterface;
use Gitter\Iterators\PromiseIterator;

/**
 * Class Room
 * @package Gitter\Models
 *
 * @property-read string $id
 * @property-read string $name
 * @property-read string $topic
 * @property-read string $uri
 * @property-read bool $oneToOne
 * @property-read int $userCount
 * @property-read int $unreadItems
 * @property-read int $mentions
 * @property-read Carbon $lastAccessTime
 * @property-read bool $lurk
 * @property-read bool $activity
 * @property-read string $url
 * @property-read string $githubType
 * @property-read string $security
 * @property-read bool $noindex
 * @property-read array $tags
 * @property-read bool $roomMember
 * @property-read int $v
 * @property-read \StdClass|null $user
 *
 * @property-read User[]|\Generator $users
 * @property-read Room[]|\Generator $channels
 */
class Room extends AbstractModel
{
    const MESSAGE_FETCH_ASC     = 'afterId';
    const MESSAGE_FETCH_DESC    = 'beforeId';

    /**
     * Room constructor.
     * @param Client $client
     * @param array|\StdClass $attributes
     */
    public function __construct(Client $client, $attributes)
    {
        parent::__construct($client, $attributes);

        $this->set('lastAccessTime', new Carbon($this->lastAccessTime));

        if ($user = $this->get('user')) {
            $this->set('user', new User($this->client, $user));
        }
    }

    /**
     * @return PromiseIterator
     */
    public function getUsers() : PromiseIterator
    {
        $perPage = 50;

        return new PromiseIterator(function($index) use ($perPage) {
            $count    = (int)$this->userCount;

            if ($index * $perPage > $count) {
                return null;
            }

            $response = $this->client
                ->createRequest()
                ->get('rooms/{id}/users', ['id' => $this->id, 'skip' => $index * $perPage, 'limit' => $perPage]);

            return $this->client->wrapResponse($response, function ($response) {
                foreach ($response as $item) {
                    yield new User($this->client, $item);
                }
            });
        });
    }

    /**
     * @return PromiseInterface
     */
    public function getChannels() : PromiseInterface
    {
        $response = $this->client
            ->createRequest()
            ->get('rooms/{id}/channels', ['id' => $this->id]);

        $this->client->wrapResponse($response, function($response) {
            foreach ($response as $item) {
                yield new Room($this->client, $item);
            }
        });
    }

    /**
     * @param string|null $messageId
     * @param string $order
     * @return PromiseIterator
     */
    public function getMessages(string $messageId = null, string $order = self::MESSAGE_FETCH_DESC) : PromiseIterator
    {
        $order          = ($order === static::MESSAGE_FETCH_ASC)
            ? static::MESSAGE_FETCH_ASC
            : static::MESSAGE_FETCH_DESC;
        $lastMessageId  = $messageId;
        $limit          = 100;
        $count          = $limit;


        return new PromiseIterator(function($skip) use (&$lastMessageId, &$count, $limit, $order) {
            $args = ($lastMessageId === null)
                ? ['id' => $this->id, 'limit' => $limit]
                : ['id' => $this->id, 'limit' => $limit, $order => $lastMessageId];

            // Get [N..N+$limit] messages
            $response = $this->client
                ->createRequest()
                ->get('rooms/{id}/chatMessages', $args);

            if ($count < $limit) {
                return null;
            }

            return $this->client->wrapResponse($response, function($messages)
            use ($order, &$count, &$lastMessageId) {
                $instance = null;

                // Reverse messages history
                if ($order === static::MESSAGE_FETCH_DESC) {
                    $messages = array_reverse($messages);
                }
                $count    = count($messages);

                // Format message and create a generator
                foreach ($messages as $message) {
                    $instance = new Message($this->client, $this, $message);
                    yield $instance;
                }

                if ($instance) {
                    $lastMessageId = $instance->id;
                }
            });
        });
    }


    /**
     * @param $text
     * @return PromiseInterface
     */
    public function sendMessage($text) : PromiseInterface
    {
        $response = $this->client
            ->createRequest()
            ->post('rooms/{id}/chatMessages', ['id' => $this->id], ['text' => (string)$text]);

        $this->client->wrapResponse($response, function($response) {
            return new Message($this->client, $this, $response);
        });
    }


    /**
     * @param \Closure $callback
     * @param \Closure $error
     * @return Transport
     */
    public function onMessage(\Closure $callback, \Closure $error = null) : Transport
    {
        return $this->stream('rooms/{id}/chatMessages', $callback, $error);
    }

    /**
     * @param \Closure $callback
     * @param \Closure $error
     * @return Transport
     */
    public function onEvent(\Closure $callback, \Closure $error = null) : Transport
    {
        return $this->stream('rooms/{id}/events', $callback, $error);
    }

    /**
     * @param $url
     * @param \Closure $callback
     * @param \Closure|null $error
     * @return Transport
     */
    protected function stream($url, \Closure $callback, \Closure $error = null) : Transport
    {
        if ($error === null) {
            $error = function(\Throwable $e) {
                throw $e;
            };
        }

        $transport = $this->client->createRequest();

        try {
            $transport
                ->send(
                    $transport
                        ->request('GET', $url, ['id' => $this->id])
                        ->withDomain(Client::GITTER_STREAM_API_DOMAIN)
                        ->asStream(true)
                )
                ->json(function ($data) use ($callback) {
                    if (
                        is_object($data) &&
                        $data instanceof \stdClass &&
                        property_exists($data, 'text')
                    ) {
                        $message = new Message($this->client, $this, $data);
                        $callback($message);
                    }
                })
                ->error(function (\Throwable $e) use ($error) {
                    $error($e);
                });

        } catch (\Throwable $e) {
            $error($e);
        }

        return $transport;
    }
}
