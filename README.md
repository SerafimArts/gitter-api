# GitterApi

### Client API

```php
$loop   = React\EventLoop\Factory::create();
$client = new Gitter\Client($loop, PERSONAL_GITTER_TOKEN);


// All rooms available for client
$promise = $client->getRooms(); // React\Promise\PromiseInterface
$promise->then(function($rooms) {
    foreach ($rooms as $room) {
        $room; // Gitter\Model\Room object
    }
});


// Room taken by gitter id like "560281040fc9f982beb1908a"
$promise = $client->getRoomById(....);
$promise->then(function(Gitter\Model\Room $room) {
    //
});


// Room taken by name like "LaravelRUS/GitterBot"
$promise = $client->getRoomByUri(....);
$promise->then(function(Gitter\Model\Room $room) {
    //
});


$loop->run();
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
$iterator = $room->getUsers(); // returns Gitter\Iterators\PromiseIterator
$iterator->fetch(function(Gitter\Model\User $user, Gitter\Iterators\PromiseIterator\Controls $controls) {
    $user; // Gitter\Model\User object

    $controls->index(); // User index
    $controls->next(); // Continue and fetch next user
});


// All subrooms (channels)
$promise = $room->getChannels();
$promise->then(function(Gitter\Model\Room $channels) {
    foreach ($channels as $channel) {
        $channel; // Gitter\Model\Room object
    }
});


// All messages in room (from latest to oldest order)
$iterator = $room->getMessages(); // returns Gitter\Iterators\PromiseIterator
$iterator->fetch(function(Gitter\Model\Message $message, Gitter\Iterators\PromiseIterator\Controls $controls) {
    $message; // Gitter\Model\Message object

    $controls->index(); // Message index
    $controls->next(); // Continue and fetch next message
});


// Send message inside room
$room->sendMessage('Hello world'); // returns React\Promise\PromiseInterface with Gitter\Model\Message object response
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
$user->getRooms()->then(function($rooms) {
    foreach ($rooms as $room) {
        $room; // Gitter\Model\Room object
    }
});


// All orgs of target user
$user->getOrganizations()->then(function($rooms) {
    foreach ($rooms as $room) {
        $room; // Gitter\Model\Room object
    }
});


// Personal one2one room
$promise = $user->getPersonalRoom(); // returns React\Promise\PromiseInterface with Gitter\Model\Room object


// Send personal message to user
$promise = $user->sendMessage('Hello world'); // returns React\Promise\PromiseInterface with Gitter\Model\Message object
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
$message->update('New text'); // returns React\Promise\PromiseInterface with Gitter\Model\Message object
```

### Streaming API

```php
$loop = \React\EventLoop\Factory::create();

$client->getRoomById(GITTER_ROOM_ID)->then(function(Room $room) {

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

});

$loop->run();
```