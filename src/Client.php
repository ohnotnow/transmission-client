<?php

namespace Ohffs\Transmission;

use Zttp\Zttp;

class Client
{
    protected $hostname;

    protected $port;

    protected $username;

    protected $password;

    protected $token;

    protected $defaultFields = [
        "id",
        "name",
        "status",
        "doneDate",
        "haveValid",
        "totalSize",
        "eta",
        "rateDownload",
        "rateUpload",
        "downloadDir",
        "percentDone",
    ];

    public function __construct($hostname = null, $port = null, $username = null, $password = null)
    {
        $this->hostname = $hostname ?: getenv('TRANSMISSION_HOSTNAME');
        $this->port = $port ?: getenv('TRANSMISSION_PORT');
        $this->username = $username ?: getenv('TRANSMISSION_USERNAME');
        $this->password = $password ?: getenv('TRANSMISSION_PASSWORD');
    }

    public function all()
    {
        $response = $this->callApi('torrent-get', ['fields' => $this->defaultFields]);
        $data = $response->json();
        $torrents = [];
        foreach ($data['arguments']['torrents'] as $torrentData) {
            $torrents[] = new TorrentEntry($torrentData);
        }
        return $torrents;
    }

    public function find($id)
    {
        $response = $this->callApi('torrent-get', ['ids' => [$id], 'fields' => $this->defaultFields]);
        if (count($response->json()['arguments']['torrents']) == 0) {
            return null;
        }
        return new TorrentEntry($response->json()['arguments']['torrents'][0]);
    }

    public function findOrFail($id)
    {
        $torrent = $this->find($id);
        if (!$torrent) {
            throw new \RuntimeException('No such torrent');
        }
        return $torrent;
    }

    public function add($filename, $paused = false)
    {
        $response = $this->callApi('torrent-add', ['filename' => $filename, 'paused' => $paused]);
        return new TorrentEntry($response->json()['arguments']['torrent-added']);
    }

    public function addPaused($filename)
    {
        return $this->add($filename, true);
    }

    public function remove($id)
    {
        $response = $this->callApi('torrent-remove', ['ids' => [$id]]);
        return true;
    }

    protected function transmissionUrl()
    {
        return $this->hostname . ':' . $this->port . '/transmission/rpc';
    }

    protected function callApi($methodName, $arguments = [])
    {
        $response = $this->buildBaseRequest()->post(
            $this->transmissionUrl(),
            [
                'method' => $methodName,
                'arguments' => $arguments
            ]
        );

        if ($response->status() == 409) {
            $this->token = $response->header('X-Transmission-Session-Id');
            $response = $this->callApi($methodName, $arguments);
        }

        if ($response->status() == 401) {
            throw new \InvalidArgumentException('Authorisation Failed');
        }

        return $response;
    }

    protected function buildBaseRequest()
    {
        $query = Zttp::withHeaders(['x-transmission-session-id' => $this->token]);
        if ($this->username) {
            $query = $query->withBasicAuth($this->username, $this->password);
        }
        return $query;
    }
}
