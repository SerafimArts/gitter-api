# GitterApi

### Client API

```php
$client = new Gitter\Client(PERSONAL_GITTER_TOKEN);


// All rooms available for client
$rooms = $client->getRooms(); // returns Generator
foreach ($rooms as $room) {
    $room; // Gitter\Model\Room object
}

// Room taken by gitter id like "560281040fc9f982beb1908a"
$room = $client->getRoomById(....); // returns Gitter\Model\Room object or null

// Room taken by name like "LaravelRUS/GitterBot"
$room = $client->getRoomByUri(....); // returns Gitter\Model\Room object or null

```

### Room API

```php
// Structure example
object(Gitter\Models\Room) (16) {
    "id"                => string(24) "560281040fc9f982beb1908a"
    "name"              => string(20) "LaravelRUS/GitterBot"
    "topic"             => string(0) ""
    "uri"               => string(20) "LaravelRUS/GitterBot"
    "userCount"         => int(45)
    "unreadItems"       => int(0)
    "mentions"          => int(0)
    "lastAccessTime"    => object(Carbon\Carbon) (3) {
        "date"              => string(26) "2016-01-25 01:42:00.258000"
        "timezone_type"     => int(2)
        "timezone"          => string(1) "Z"
    }
    "lurk"              => bool(false)
    "activity"          => bool(true)
    "url"               => string(21) "/LaravelRUS/GitterBot"
    "githubType"        => string(4) "REPO"
    "security"          => string(6) "PUBLIC"
    "tags"              => array(0) { ... }
    "roomMember"        => bool(true)
    "extra"             => object(stdClass) (0) { ... }
}
```

```php
$room; // Gitter\Model\Room object

// All users inside room
$users = $room->getUsers(); // returns Generator
foreach ($users as $user) {
    $user; // Gitter\Model\User object
}

// All room subrooms (channels)
$channels = $room->getChannels(); // returns Generator
foreach ($channels as $channel) {
    $channel; // Gitter\Model\Room object
}

// All messages in room (from latest to oldest order)
$messages = $room->getMessages(); // returns Generator
foreach ($messages as $message) {
    $message; // Gitter\Model\Message object
}

// Send message inside room
$room->sendMessage('Hello world'); // returns Gitter\Model\Message object
```

### User API

```php
// Structure example
object(Gitter\Models\User) (8) {
    "id"                => string(24) "52fcfccb5e986b0712ef71d0"
    "username"          => string(11) "SerafimArts"
    "displayName"       => string(17) "Kirill Nesmeyanov"
    "url"               => string(12) "/SerafimArts"
    "avatarUrlSmall"    => string(57) "https://avatars2.githubusercontent.com/u/2461257?v=3&s=60"
    "avatarUrlMedium"   => string(58) "https://avatars2.githubusercontent.com/u/2461257?v=3&s=128"
    "v"                 => int(6)
    "gv"                => string(1) "3"
}

```

```php
$user; // Gitter\Model\User object

// All rooms of target user
$rooms = $user->getRooms(); // returns Generator
foreach ($rooms as $room) {
    $room; // Gitter\Model\Room object
}

// All orgs of target user
$rooms = $user->getOrganizations(); // returns Generator
foreach ($rooms as $room) {
    $room; // Gitter\Model\Room object
}

// Personal one2one room
$pm = $user->getPersonalRoom(); // returns Gitter\Model\Room object

// Send personal message to user
$user->sendMessage('Hello world'); // returns Gitter\Model\Message object
```

### Message API

```php
// Structure example
object(Gitter\Models\Message) (14) {
    "id"            => string(24) "56a58030dc33b33c7547a4fa"
    "text"          => string(9) "тест2"
    "html"          => string(9) "тест2"
    "sent"          => object(Carbon\Carbon) (3) {
        "date"          => string(26) "2016-01-25 01:53:40.000000"
        "timezone_type" => int(3)
        "timezone"      => string(3) "UTC"
    }
    "fromUser"      => object(Gitter\Models\User) (8) { ... }
    "unread"        => bool(true)
    "readBy"        => int(0)
    "urls"          => array(0) { ... }
    "mentions"      => array(0) { ... }
    "issues"        => array(0) { ... }
    "meta"          => array(0) { ... }
    "v"             => int(1)
    "room"          => object(Gitter\Models\Room) (16) { ... }
    "editedAt"      => object(Carbon\Carbon) (3) {
        "date"          => string(26) "2016-01-25 01:53:40.000000"
        "timezone_type" => int(3)
        "timezone"      => string(3) "UTC"
    }
}
```


```php
$message; // Gitter\Model\Message object

// Update message text
$message->update('New text'); // returns Gitter\Model\Message object
```

### Streaming API

```php
$loop = \React\EventLoop\Factory::create();

$room = $client->getRoomById(GITTER_ROOM_ID);


// Listen messages events
$room->onMessage($loop, function(Message $message) {
    $message; // Gitter\Model\Message object
}, function(Throwable $error) {
    $error;
});


// Listen activity events
$room->onEvent($loop, function(Message $message) {
    $message; // Gitter\Model\Message object
}, function(Throwable $error) {
    $error;
});

$loop->run();
```