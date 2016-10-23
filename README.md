# Gitter API Client 3.0 

[![https://travis-ci.org/SerafimArts/gitter-api](https://travis-ci.org/SerafimArts/gitter-api.svg)](https://travis-ci.org/SerafimArts/gitter-api/builds)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SerafimArts/gitter-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SerafimArts/gitter-api/?branch=master)

- [Version 2.1.x](https://github.com/SerafimArts/gitter-api/tree/967ef646afa3181fbb10ec6669538c4911866731)
- [Version 2.0.x](https://github.com/SerafimArts/gitter-api/tree/8ad7f4d06c5f8196ada5798799cd8c1d5f55a974)
- [Version 1.1.x](https://github.com/SerafimArts/gitter-api/tree/26c3640a1d933db8ad27bd3c10f8bc42ff936c47)
- [Version 1.0.x](https://github.com/SerafimArts/gitter-api/tree/f955ade02128e868d494baf0acc021bc257c1807)

## Installation

`composer require serafim/gitter-api`

## Adapters

- **React** ([reactphp/react](https://github.com/reactphp/react))
    - Sync: `array`
    - Async: `React\Promise\PromiseInterface`
    - Streaming: `Gitter\Support\Observer`
    
    Installation: **included (default)**
    
- **GuzzleHttp** ([guzzle/guzzle](https://github.com/guzzle/guzzle)) 
    - Sync: `array`
    - Async: `GuzzleHttp\Promise\PromiseInterface` (blocking!)
    - Streaming: `\Generator` (blocking!)
    
    Installation: `composer require guzzlehttp/guzzle`


## Creating a Gitter Client


```php
use Gitter\Client;

$client = new Client($token); 
// OR
$client = new Client($token, $logger); // $logger are instance of \Psr\Log\LoggerInterface

// ... SOME ACTIONS ...

$client->connect(); // Locks current runtime and starts an EventLoop
```

## Resources

Example: `$client->resource->action(...)` or `$client->resource->fetchType->action(...)`

- `resource` are one of `"rooms"`, `"users"`, `"groups"` or `"messages"`
- `fetchType` are one of `"sync"`, `"async"` or `"stream"`
- `action` are specify for every resource

```php
$response = $client->rooms->all(); // "rooms" - resource, "all" - action

foreach ($response as $room) {
    var_dump($room);
}

// ...

$client->connect();
```

### Sync 

Sync requests are block event loop "tick" 
    and fetch all data from external API resource. 

```php
$response = $client->rooms->sync->all(); // array

foreach ($response as $room) {
    var_dump($room);
}

$client->connect();
```

### Async 

Async requests are not blocks an event loop and returns a Promise object (callback).
After fetching all data Promise will be close.

```php
$promise = $client->rooms->async->all(); // Promise

$promise->then(function($response) { 
    foreach ($response as $room) {
        var_dump($room);
    }
});

$client->connect();
```

### Streaming 

Streaming requests like an async but cant be resolved. Usually for long-polling answers. 

```php
$observer = $client->rooms->stream->all(); // Observer

$observer->subscribe(function($response) {
    foreach ($response as $room) {
        var_dump($room);
    }
});

$client->connect();
```

## Available resources

### Groups

List groups the current user is in.

- `$client->groups->all()`

List of rooms nested under the specified group.

- `$client->groups->rooms(string $roomId)`

### Messages

List of messages in a room in historical reversed order. **Only synchronous driver** 

- `$client->messages->all(string $roomId[, string $query]): \Generator`

There is also a way to retrieve a single message using its id.

- `$client->messages->find(string $roomId, string $messageId)`

Send a message to a room.

- `$client->messages->create(string $roomId, string $content)`

Update a message.

- `$client->messages->update(string $roomId, string $messageId, string $content)`

Delete a message.

- `$client->messages->delete(string $roomId, string $messageId)`

### Rooms

List rooms the current user is in.

- `$client->rooms->all([string $query])`

Join user into a room.

- `$client->rooms->joinUser(string $roomId, string $userId)`

Join current user into a room.

- `$client->rooms->join(string $roomId)`

Join current user into a room by room name (URI).

- `$client->rooms->joinByName(string $name)`

Find room by room name (URI).

- `$client->rooms->findByName(string $name)`

Kick user from target room.

- `$client->rooms->kick(string $roomId, string $userId)`

This can be self-inflicted to leave the the room and remove room from your left menu.

- `$client->rooms->leave(string $roomId)`

Sets up a new topic of target room.

- `$client->rooms->topic(string $roomId, string $topic)`

Sets the room is indexed by search engines.

- `$client->rooms->searchIndex(string $roomId, bool $enabled)`

Sets the tags that define the room

- `$client->rooms->tags(string $roomId, array $tags)`

If you hate one of the rooms - you can destroy it!

- `$client->rooms->delete(string $roomId)`

List of users currently in the room. **Only synchronous driver**

- `$client->rooms->users(string $roomId[, string $query]: \Generator`

Use the streaming API to listen events. **Only streaming driver**

- `$client->rooms->events(string $roomId): Observer`

Use the streaming API to listen messages. **Only streaming driver**

- `$client->rooms->messages(string $roomId): Observer`

### Users

Returns the current user logged in.

- `$client->users->current(): array`

List of Rooms the user is part of.

- `$client->users->rooms([string $userId])`

You can retrieve unread items and mentions using the following endpoint.

- `$client->users->unreadItems(string $roomId[, string $userId])`

There is an additional endpoint nested under rooms that you can use to mark chat messages as read

- `$client->users->markAsRead(string $roomId, array $messageIds[, string $userId])`

List of the user's GitHub Organisations and their respective Room if available.

- `$client->users->orgs([string $userId])`

List of the user's GitHub Repositories and their respective Room if available.

- `$client->users->repos([string $userId])`

List of Gitter channels nested under the user.

- `$client->users->channels([string $userId])`

## Custom routing

```php
$route = Route::get('rooms/{roomId}/chatMessages')
    ->with('roomId', $roomId)
    ->toStream();
    
// Contains "GET https://stream.gitter.im/v1/rooms/.../chatMessages" url

$client->request->stream->to($route)->subscribe(function($message) {
     var_dump($message);
    // Subscribe on every message in target room (Realtime subscribtion)
});

$client->connect();
```

Available route methods:

- `Route::get(string $route)` - GET http method
- `Route::post(string $route)` - POST http method
- `Route::put(string $route)` - PUT http method
- `Route::patch(string $route)` - PATCH http method
- `Route::delete(string $route)` - DELETE http method
- `Route::options(string $route)` - OPTIONS http method
- `Route::head(string $route)` - HEAD http method
- `Route::connect(string $route)` - CONNECT http method
- `Route::trace(string $route)` - TRACE http method

Route arguments:

- `$route->with(string $key, string $value)` - Add route or GET query parameter
- `$route->withMany(array $parameters)` - Add route or GET query parameters
- `$route->withBody(string $key, string $value)` - Add POST, PUT, DELETE, etc body parameter


See more info about API into [Documentation](https://developer.gitter.im/docs/welcome)