WIP

# Basic PHP API wrapper for Transmission

This is a (for now) very basic wrapper around the [Transmission](https://transmissionbt.com/) bittorrent
client's API.

For now it only supports getting a list of all current torrents, fetching a specific torrent and adding
a new torrent.  This was all I needed for my purposes in [transcopy](https://github.com/ohnotnow/transcopy).

If you are a Laravel user, you might want to check out the [Laravel Wrapper](https://github.com/ohnotnow/laravel-transmission).

# Installing

Assuming you have [composer](https://getcomposer.org/) available :

```
composer require ohnotnow/transmission-client
```

# Usage

```php
$client = new \Ohffs\Transmission\Client('127.0.0.1', 9091, 'username', 'password');

$allTorrents = $client->all(); // returns an array of TorrentEntry's

$singleTorrent = $client->find(1234); // returns a single TorrentEntry or null

$borkedTorrent = $client->find(-1); // returns null

$ohno = $client->findOrFail(-1); // throws a RuntimeException

$newTorrent = $client->add('/path/to/an/exciting.torrent'); // returns a TorrentEntry

$pausedTorrent = $client->addPaused('/path/to/an/exciting.torrent'); // returns a TorrentEntry and pauses it in transmission

$client->remove(1234); // removes a torrent from transmission - returns a boolean
```

The `TorrentEntry` is a small class which wraps the data that comes back from Transmission :

```php
$singleTorrent = $client->find(1234);
var_dump($singleTorrent->toArray());
/*
 'name' => 'Some Exciting File',
 'id' => 1234,
 'doneDate' => 0,
 'eta' => 1000,
 'haveValid' => 0,
 'rateDownload' => 0,
 'rateUpload' => 0,
 'status' => 2,
 'totalSize' => 364514248,
 'downloadDir' => '/tmp/torrents',
 'percentDone' => 0.3,
*/

// And you can also get those as attributes on the object, eg :

echo $torrent->name;
// 'Some Exciting File'
```

Instead of passing the host, username etc when creating a client, you can set some environment variables which will be used :

```
TRANSMISSION_HOST=127.0.0.1
TRANSMISSION_PORT=9091
TRANSMISSION_USERNAME=whatever
TRANSMISSION_PASSWORD=secret
```
