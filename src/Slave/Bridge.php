<?php
namespace Bee\Websocket\Slave;

/**
 * 从节点与主节点通信连接服务
 *
 * @package Bee\Websocket\Slave
 */
class Bridge
{
    /**
     * @var string
     */
    protected $host;

    /**
     * @var int
     */
    protected $port;

    /**
     * @var callable
     */
    protected $callback;

    /**
     * @var int
     */
    protected $heartbeat;

    /**
     * @var \swoole_http_client
     */
    protected $client;

    /**
     * Bridge constructor.
     *
     * @param array $config
     * @param callable $callback
     * @param int $heartbeat
     */
    public function __construct(array $config, callable $callback, $heartbeat = 1)
    {
        $this->host      = $config['host'];
        $this->port      = $config['port'];
        $this->callback  = $callback;
        $this->heartbeat = $heartbeat;
    }

    /**
     * 连接主节点
     */
    public function connect()
    {
        $this->client = new \swoole_http_client($this->host, $this->port);
        $this->client->on('message', function ($cli, $frame) {
            call_user_func($this->callback, unserialize($frame->data));
        });
        $this->client->upgrade('/', function ($cli) {
        });
    }

    /**
     * 向主节点发送消息
     *
     * @param $data
     */
    public function send(array $data)
    {
        if (!$this->client) {
            $this->connect();
        }

        $this->client->push(serialize($data));
    }

    /**
     * 连客户端连接信息注册至主节点
     *
     * @param array $fds 连接对象集[uuid => fd]
     */
    public function registerConnect(array $fds)
    {
        $this->send(
            [
                'a' => 100,
                'c' => $fds,
            ]
        );
    }

    /**
     * 向主节点发送广播请求
     *
     * @param array $target 连接对象集[uuid]
     * @param string $data 带广播数据体
     */
    public function broadcast(array $target, string $data)
    {
        $this->send(
            [
                'a' => 101,
                'u' => $target,
                'd' => $data,
            ]
        );
    }
}
