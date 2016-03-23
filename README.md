# GitterApi

### Installation

- `composer require serafim/gitter-api`

### Creating client

```php
$client = new Client(string $token);
```

### Sync and async methods

All methods returns a Promise, like this:

```php
$client->someMethod() : Promise;
```

Promises returns result after method was completed successfully. 
You can stack promises and wait result:

```php
yield Amp\all([$promise1, $promise2, $promise3]);
```

Or using sync request:

```php
$result = $promise->wait();

// Or

$result = Amp\wait($promise);
```

> Read more about requests concurrency: http://amphp.org/docs/amp/managing-concurrency.html

### Http methods

##### List rooms the current user is in

All the parameters are optional:
- q: Search query

- param: array $args
- return: Promise

```php
$client->http->getRooms([array $args]) : Promise;
```

##### List of users currently in the room

All the parameters are optional:
- q: Search query
- skip: Skip n users.
- limit: maximum number of users to return (default 30).

- param: string $roomId Room id
- param: array $args
- return: Promise

```php
$client->http->getRoomUsers(string $roomId [, array $args]) : Promise;
```

##### List of Gitter channels (rooms) nested under the specified room

- param: string $roomId Room id
- return: Promise

```php
$client->http->getRoomChannels(string $roomId) : Promise;
```

##### Join to room

To join a room you'll need to provide a URI for it.

Said URI can represent a GitHub Org, a GitHub Repo or a Gitter Channel.
- If the room exists and the user has enough permission to access it, it'll be added to the room.
- If the room doesn't exist but the supplied URI represents a GitHub Org or GitHub Repo the user
is an admin of, the room will be created automatically and the user added.

- param: string $uri Required URI of the room you would like to join
- return: Promise

```php
$client->http->joinRoom(string $uri) : Promise;
```

*Alias*

- param: string $uri Room uri
- return: Promise

```php
$client->http->getRoomByUri(string $uri) : Promise;
```

##### Get room by id

- param: string $roomId Room id
- return: Promise

```php
$client->http->getRoomById(string $roomId) : Promise;
```

##### Remove a user from a room. 

This can be self-inflicted to leave the the room and remove room from your left menu.

- param: string $roomId
- param: string $userId
- return: Promise

```php
$client->http->removeUserFromRoom(string $roomId, string $userId) : Promise;
```

##### Update room information.

Parameters:
- topic: Room topic.
- noindex: Whether the room is indexed by search engines
- tags: Tags that define the room.
- param: string $roomId Room id
- param: array $args
- return: Promise

```php
$client->http->updateRoomInfo(string $roomId [, array $args]) : Promise;
```

##### Delete room

- param: string $roomId Room id
- return: Promise

```php
$client->http->deleteRoom(string $roomId) : Promise;
```

##### List of messages in a room

All the parameters are optional:
- skip: Skip n messages
- beforeId: Get messages before beforeId
- afterId: Get messages after afterId
- aroundId: Get messages around aroundId including this message
- limit: Maximum number of messages to return
- q: Search query

- param: string $roomId Room id
- param: array $args
- return: Promise

```php
$client->http->getMessages(string $roomId [, array $args]) : Promise;
```

There is also a way to retrieve a single message using its id.

- param: string $roomId Room id
- param: string $messageId Message id
- return: Promise

```php
$client->http->getMessage(string $roomId, string $messageId) : Promise;
```

##### Send a message to a room.

- param: string $roomId Room id
- param: string $text Message text
- return: Promise

```php
$client->http->sendMessage(string $roomId, string $text) : Promise;
```

##### Update a message.

- param: string $roomId Room id
- param: string $messageId Message id
- param: string $text Required Body of the message.
- return: Promise

```php
$client->http->updateMessage(string $roomId, string $messageId, string $text) : Promise;
```

##### Get the current user.

- return: Promise

```php
$client->http->getCurrentUser() : Promise;
```

##### Get user by id.

- param: string $userId User id
- return: Promise

```php
$client->http->getUser(string $userId) : Promise;
```

##### List of Rooms the user is part of.

- param: string $userId User id
- return: Promise

```php
$client->http->getUserRooms(string $userId) : Promise;
```

##### Take unread items

- param: string $userId
- param: string $roomId
- return: Promise

```php
$client->http->getUserUnreadItems(string $userId, string $roomId) : Promise;
```

##### Read unread items

There is an additional endpoint nested under rooms that you can use to mark chat messages as read.
Parameters:
- chat: Array of chatIds.
- param: string $userId User id
- param: string $roomId Room id
- param: array $args
- return: Promise

```php
$client->http->readItems(string $userId, string $roomId [, array $args]) : Promise;
```

##### User's orgs

List of the user's GitHub Organisations and their respective Room if available.
- param: string $userId User id
- return: Promise

```php
$client->http->getUserOrgs(string $userId) : Promise;
```

##### User's repos

List of the user's GitHub Repositories and their respective Room if available.

> It'll return private repositories if the current user has granted Gitter privileges to access them.

- param: string $userId User id
- return: Promise

```php
$client->http->getUserRepos(string $userId) : Promise;
```

##### User's channels

List of Gitter channels nested under the current user.
- param: string $userId User id
- return: Promise

```php
$client->http->getUserChannels(string $userId) : Promise;
```
