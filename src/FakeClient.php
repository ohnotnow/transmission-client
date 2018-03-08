<?php

namespace Ohffs\Transmission;

use Zttp\Zttp;

class FakeClient
{
    protected $hostname;

    protected $port;

    protected $username;

    protected $password;

    protected $torrents;

    protected $filenames;

    public function __construct($hostname = null, $port = null, $username = null, $password = null)
    {
        $this->hostname = $hostname ?: getenv('TRANSMISSION_HOSTNAME');
        $this->port = $port ?: getenv('TRANSMISSION_PORT');
        $this->username = $username ?: getenv('TRANSMISSION_USERNAME');
        $this->password = $password ?: getenv('TRANSMISSION_PASSWORD');
        $this->torrents = [];
        $this->filenames = [];
    }

    public function authenticate($username = null, $password = null)
    {
        $this->username = $username ?: config('TRANSMISSION_USERNAME');
        $this->password = $password ?: config('TRANSMISSION_PASSWORD');
        return $this;
    }

    public function all()
    {
        return $this->torrents;
    }

    public function find($id)
    {
        foreach ($this->torrents as $torrent) {
            if ($torrent->id === $id) {
                return $torrent;
            }
        }
        return null;
    }

    public function findOrFail($id)
    {
        $torrent = $this->find($id);
        if (! $torrent) {
            throw new \RuntimeException('No such torrent');
        }
        return $torrent;
    }

    public function add($filename, $paused = false)
    {
        try {
            $fileInfo = $this->extractTorrentInfo($filename);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException('invalid or corrupt torrent file');
        }
        if (array_key_exists($filename, $this->filenames)) {
            return $this->find($this->filenames[$filename]);
        }
        $entry = new TorrentEntry([
            'name' => $fileInfo['info']['name'],
            'id' => rand(1, 1000000),
            'doneDate' => 0,
            'eta' => -1,
            'haveValid' => 0,
            'rateDownload' => 0,
            'rateUpload' => 0,
            'status' => 2,
            'totalSize' => 364514248,
            'downloadDir' => '/tmp',
            'percentDone' => 0,
        ]);
        $this->torrents[] = $entry;
        $this->filenames[$filename] = $entry->id;
        return $entry;
    }

    public function addPaused($filename)
    {
        return $this->add($filename, true);
    }

    public function remove($id)
    {
        foreach ($this->torrents as $key => $torrent) {
            if ($torrent->id === $id) {
                unset($this->torrents[$key]);
            }
        }
    }

    protected function extractTorrentInfo($filename)
    {
        $encoder = new \PHP\BitTorrent\Encoder();
        $decoder = new \PHP\BitTorrent\Decoder($encoder);

        return $decoder->decodeFile($filename);
    }
}
