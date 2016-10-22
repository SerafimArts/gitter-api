# Gitter 

[![https://travis-ci.org/SerafimArts/gitter-api](https://travis-ci.org/SerafimArts/gitter-api.svg)](https://travis-ci.org/SerafimArts/gitter-api/builds)

Features:
- Syncronious requests
- Non blocking async requests
- Realtime non blocking streaming requests

Drivers:
- React ([reactphp/react](https://github.com/reactphp/react))
    - Sync: array
    - Async: `React\Promise\PromiseInterface`
    - Streaming: `Gitter\Support\Observer`
    
    Installation: **included (default)**
    
- GuzzleHttp ([guzzle/guzzle](https://github.com/guzzle/guzzle)) 
    - Sync: arrays
    - Async: `GuzzleHttp\Promise\PromiseInterface` (blocking!)
    - Streaming: `\Generator` (blocking!)
    
    Installation: `composer require guzzlehttp/guzzle`


> This is **not stable** master branch.
> See the [latest stable version](https://github.com/SerafimArts/gitter-api/tree/967ef646afa3181fbb10ec6669538c4911866731)


## Short API description 

### Creating a Gitter Client

```php
use Gitter\Client;

$client = new Client($token); 
// OR
$client = new Client($token, $logger); // $logger are instance of \Psr\Log\LoggerInterface

// ... SOME ACTIONS ...

$client->connect(); // Locks current runtime and starts an EventLoop
```

### Resources

1) `$client->resource->action(...)`

Where `resource` are one of `"rooms"`, `"users"`, `"groups"` or `"messages"`; `action` are specify for every resource.

```php
$response = $client->rooms->all(); // "rooms" - resource, "all" - action

foreach ($response as $room) {
    var_dump($room);
}

// ...

$client->connect();
```

2) `$client->resource->fetchType->action(...)`

Where `fetchType` are one of `"sync"`, `"async"` or `"stream"`.

#### Sync 

Sync requests are block event loop "tick" 
    and fetch all data from external API resource. 

```php
$response = $client->rooms->sync->all(); // array

foreach ($response as $room) {
    var_dump($room);
}

$client->connect();
```

#### Async 

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

#### Streaming 

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

### Custom routing

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