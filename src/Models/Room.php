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
use Gitter\Support\Fiber;
use Gitter\Http\Stream\Connection;
use React\EventLoop\LoopInterface;
use Gitter\Http\Stream\StreamConnectionException;
use React\Promise\PromiseInterface;

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
     * @return \Generator
     * @TODO
     */
    public function getUsers() : \Generator
    {
        yield from (new Fiber())
            ->limit((int)$this->userCount)
            ->fetch(function ($skip) {
                $response = $this->client
                    ->createRequest()
                    ->get('rooms/{id}/users', ['id' => $this->id, 'skip' => $skip]);

                foreach ($response as $item) {
                    yield new User($this->client, $item);
                }
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
     * @return \Generator
     * @TODO
     */
    public function getMessages() : \Generator
    {
        $lastMessageId  = null;
        $limit          = 100;

        yield from (new Fiber())
            ->fetch(function($skip) use ($limit, &$lastMessageId) {
                $instance       = null;
                $args           = ($lastMessageId === null)
                    ? ['id' => $this->id, 'limit' => $limit]
                    : ['id' => $this->id, 'limit' => $limit, 'beforeId' => $lastMessageId];

                // If last messages chin less than $limit atop an iteration
                if(!!($skip % $limit)) { return []; }

                // Get [N..N+$limit] messages
                $messages       = $this->client
                    ->createRequest()
                    ->get('rooms/{id}/chatMessages', $args)
                    ->toArray();

                // Reverse messages history
                $messages = array_reverse($messages);

                // Format message and create a generator
                foreach ($messages as $message) {
                    $instance = new Message($this->client, $this, $message);
                    yield $instance;
                }

                // Applies last message id for next iteration tick
                if ($instance) {
                    $lastMessageId = $instance->id;
                }
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
     * @return Room
     */
    public function onMessage(\Closure $callback, \Closure $error = null) : Room
    {
        return $this->stream('rooms/{id}/chatMessages', $callback, $error);
    }

    /**
     * @param \Closure $callback
     * @param \Closure $error
     * @return Room
     */
    public function onEvent(\Closure $callback, \Closure $error = null) : Room
    {
        return $this->stream('rooms/{id}/events', $callback, $error);
    }

    /**
     * @param $url
     * @param \Closure $callback
     * @param \Closure|null $error
     * @return Room
     */
    protected function stream($url, \Closure $callback, \Closure $error = null) : Room
    {
        if ($error === null) {
            $error = function(\Throwable $e) {
                throw $e;
            };
        }

        try {
            $transport = $this->client->createRequest();
            $transport
                ->send(
                    $transport
                        ->request('GET', $url, ['id' => $this->id])
                        ->withDomain(Client::GITTER_STREAM_API_DOMAIN)
                        ->asStream(true)
                )
                ->json(function ($data) use ($callback) {
                    $message = new Message($this->client, $this, $data);
                    $callback($message);
                })
                ->error(function (\Throwable $e) use ($error) {
                    $error($e);
                });

        } catch (\Throwable $e) {
            $error($e);
        }

        return $this;
    }
}
