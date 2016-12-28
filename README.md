# Gitter API Client 4.0 

[![Latest Stable Version](https://poser.pugx.org/serafim/gitter-api/v/stable)](https://packagist.org/packages/serafim/gitter-api)
[![https://travis-ci.org/SerafimArts/gitter-api](https://travis-ci.org/SerafimArts/gitter-api.svg)](https://travis-ci.org/SerafimArts/gitter-api/builds)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/SerafimArts/gitter-api/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/SerafimArts/gitter-api/?branch=master)
[![License](https://poser.pugx.org/serafim/gitter-api/license)](https://packagist.org/packages/serafim/gitter-api)
[![Total Downloads](https://poser.pugx.org/serafim/gitter-api/downloads)](https://packagist.org/packages/serafim/gitter-api)


- [Version 3.0.x](https://github.com/SerafimArts/gitter-api/commit/5bf22f2b5bbc517600937fbbaa44037a89688a82)
- [Version 2.1.x](https://github.com/SerafimArts/gitter-api/tree/967ef646afa3181fbb10ec6669538c4911866731)
- [Version 2.0.x](https://github.com/SerafimArts/gitter-api/tree/8ad7f4d06c5f8196ada5798799cd8c1d5f55a974)
- [Version 1.1.x](https://github.com/SerafimArts/gitter-api/tree/26c3640a1d933db8ad27bd3c10f8bc42ff936c47)
- [Version 1.0.x](https://github.com/SerafimArts/gitter-api/tree/f955ade02128e868d494baf0acc021bc257c1807)

## Installation

`composer require serafim/gitter-api`

## Creating a Gitter Client


```php
use Gitter\Client;

$client = new Client($token); 
// OR
$client = new Client($token, $logger); // $logger are instance of \Psr\Log\LoggerInterface

// ... SOME ACTIONS ...

$client->connect(); // Locks current runtime and starts an EventLoop (for streaming requests)
```

## Resources

```php
// $client = new \Gitter\Client('token');

$client->groups(); // Groups resource
$client->messages(); // Messages resource
$client->rooms(); // Rooms resource
$client->users(); // Users resource
```

### Example

```php
$response = $client->rooms()->all();

foreach ($response as $room) {
    var_dump($room);
}
```

### Streaming 

```php
$observer = $client->rooms()->messages('roomId'); // Observer

$observer->subscribe(function ($message) {
    var_dump($message);
});

// Connect to stream!
$client->connect();
```

## Available resources

### Groups

List groups the current user is in.

- `$client->groups()->all(): array`

List of rooms nested under the specified group.

- `$client->groups()->rooms(string $roomId): array`

### Messages

List of messages in a room in historical reversed order.

- `$client->messages()->all(string $roomId[, string $query]): \Generator`

There is also a way to retrieve a single message using its id.

- `$client->messages()->find(string $roomId, string $messageId): array`

Send a message to a room.

- `$client->messages()->create(string $roomId, string $content): array`

Update a message.

- `$client->messages()->update(string $roomId, string $messageId, string $content): array`

Delete a message.

- `$client->messages()->delete(string $roomId, string $messageId): array`

### Rooms

List rooms the current user is in.

- `$client->rooms()->all([string $query]): array`

Join user into a room.

- `$client->rooms()->joinUser(string $roomId, string $userId): array`

Join current user into a room.

- `$client->rooms()->join(string $roomId): array`

Join current user into a room by room name (URI).

- `$client->rooms()->joinByName(string $name): array`

Find room by room name (URI).

- `$client->rooms()->findByName(string $name): array`

Kick user from target room.

- `$client->rooms()->kick(string $roomId, string $userId): array`

This can be self-inflicted to leave the the room and remove room from your left menu.

- `$client->rooms()->leave(string $roomId): array`

Sets up a new topic of target room.

- `$client->rooms()->topic(string $roomId, string $topic): array`

Sets the room is indexed by search engines.

- `$client->rooms()->searchIndex(string $roomId, bool $enabled): array`

Sets the tags that define the room

- `$client->rooms()->tags(string $roomId, array $tags): array`

If you hate one of the rooms - you can destroy it!

- `$client->rooms()->delete(string $roomId): array`

List of users currently in the room. 

- `$client->rooms()->users(string $roomId[, string $query]: \Generator`

Use the streaming API to listen events. 

- `$client->rooms()->events(string $roomId): Observer`

Use the streaming API to listen messages. 

- `$client->rooms()->messages(string $roomId): Observer`

### Users

Returns the current user logged in.

- `$client->users()->current(): array`
- `$client->users()->currentUserId(): string`

List of Rooms the user is part of.

- `$client->users()->rooms([string $userId]): array`

You can retrieve unread items and mentions using the following endpoint.

- `$client->users()->unreadItems(string $roomId[, string $userId]): array`

There is an additional endpoint nested under rooms that you can use to mark chat messages as read

- `$client->users()->markAsRead(string $roomId, array $messageIds[, string $userId]): array`

List of the user's GitHub Organisations and their respective Room if available.

- `$client->users()->orgs([string $userId]): array`

List of the user's GitHub Repositories and their respective Room if available.

- `$client->users()->repos([string $userId]): array`

List of Gitter channels nested under the user.

- `$client->users()->channels([string $userId]): array`

## Custom WebHook Notifications

Create a "Custom Webhook": 
- Open your chat
- Click on "Room Settings" button
- Click on "Integrations"
- Select "Custom"
- Remember yor Hook Id, like `2b66cf4653faa342bbe8` inside `https://webhooks.gitter.im/e/` url.

```php
$client->notify($hookId)
    // ->error($message) - Send "Error" message
    // ->info($message) - Send "Info" message
    // ->withLevel(...) - Sets up level
    ->send('Your message with markdown'); // Send message with markdown content
```

## Custom routing

```php
$route = Route::get('rooms/{roomId}/chatMessages')
    ->with('roomId', $roomId)
    ->toStream();
    
// Contains "GET https://stream.gitter.im/v1/rooms/.../chatMessages" url

$client->viaStream()->request($route)->subscribe(function($message) {
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
