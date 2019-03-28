<?php
namespace Bee\Websocket\Slave;

use Swoole\Coroutine\Http\Client;

class Master
{
    protected $host;

    protected $port;

    protected $heartbeat;

    /**
     * @var Client
     */
    protected $client;

    public function __construct(string $host, int $port, $heartbeat = 1)
    {
        $this->host      = $host;
        $this->port      = $port;
        $this->heartbeat = $heartbeat;
    }

    public function connect()
    {
        $this->client = new Client($this->host, $this->port);
        $this->client->upgrade('/');
        $this->client->push('do connect');

        while (true) {
            $data = $this->client->recv();
        }
    }

    public function send($data)
    {
        echo '------------- register ------------' . "\n";
        echo $data . "\n\n";
        $this->client->push($data);
    }
}
