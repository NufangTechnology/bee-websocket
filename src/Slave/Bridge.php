<?php
namespace Bee\Websocket\Slave;

use Bee\Websocket\Exception;
use Swoole\Coroutine;
use Swoole\Coroutine\Client;

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
     * @var Client
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
     *
     * @throws Exception
     */
    public function connect()
    {
        $this->client = new Client(SWOOLE_SOCK_TCP);
        $this->client->connect($this->host, $this->port);

        if (!$this->client->isConnected()) {
            throw new Exception("Connect to '{$this->host}:{$this->port}' failed!", $this->client->errCode);
        }
    }

    /**
     * 尝试断线重连
     *
     * @param int $tryNum
     * @return true
     * @throws Exception
     */
    public function reconnect($tryNum = 0)
    {
        try {
            $this->connect();

            return true;
        } catch (Exception $e) {

            // 断线重连最多尝试10次
            if ($tryNum > 10) {
                throw $e;
            }

            // 记录 notice
            trigger_error($e->getMessage());
        }

        // 当前协程进入睡眠
        // 每次睡眠时间等于重试次数(即每次沉睡时间延长一秒，10次最多为55秒)：1 + 2 + 3...
        Coroutine::sleep($tryNum);

        // 重新尝试连接
        $this->reconnect(++$tryNum);
    }

    /**
     * 接收主节点数据
     *
     * @throws Exception
     */
    public function receive()
    {
        while (true) {
            $data = $this->client->recv();

            // 接收到的主节点数据为空，检查是否连接断开
            // 如果连接断开进行自动重连
            if (empty($data)) {
                $this->reconnect();
            } else {
                call_user_func($this->callback, unserialize($data));
            }
        }
    }

    /**
     * 向主节点发送消息
     *
     * @param array $data
     * @throws Exception
     */
    public function send(array $data)
    {
        if (!$this->client->isConnected()) {
            $this->reconnect();
        }

        $this->client->send(serialize($data));
    }

    /**
     * 连客户端连接信息注册至主节点
     *
     * @param array $fds 连接对象集[uuid => fd]
     * @throws Exception
     */
    public function register(array $fds)
    {
        $this->send(
            [
                'a' => SLAVE_ID,
                'c' => 101,
                'u' => $fds,
            ]
        );
    }

    /**
     * 向主节点发送广播请求
     *
     * @param array $target 连接对象集[uuid]
     * @param mixed $data 带广播数据体
     * @throws Exception
     */
    public function broadcast(array $target, $data)
    {
        $this->send(
            [
                'a' => SLAVE_ID,
                'c' => 102,
                'u' => $target,
                'd' => $data,
            ]
        );
    }

    /**
     * 发主节点发送单播请求
     *
     * @param string $uuid
     * @param mixed $data
     * @throws Exception
     */
    public function unicast($uuid, $data)
    {
        $this->broadcast([$uuid], $data);
    }
}
