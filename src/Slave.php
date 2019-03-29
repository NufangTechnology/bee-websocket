<?php
namespace Bee\Websocket;

use Swoole\Server\Task;
use Bee\Websocket\Slave\Bridge;

/**
 * 从节点服务
 *
 * @package Bee\Websocket
 * @property \Swoole\WebSocket\Server $swoole
 */
abstract class Slave extends Server
{
    /**
     * @var Bridge
     */
    protected $bridge;

    /**
     * Slave constructor.
     *
     * @param array $runtime
     * @throws Exception
     */
    public function __construct(array $runtime)
    {
        if (!isset($runtime['slave'])) {
            throw new Exception('slave配置不能为空');
        }

        // 需要主节点 host, port 配置参数
        // 从节点需要连接至主节点，必须拥有主节点配置
        if (!isset($runtime['master'])) {
            throw new Exception('master配置不能为空');
        }

        $this->bridge = new Bridge($runtime['master'], [$this, 'masterNotify']);

        // 传入服务启用配置进行示例化
        parent::__construct($runtime['slave']);
    }

    /**
     * 异步任务处理
     *
     * @param \Swoole\WebSocket\Server $server
     * @param Task $task
     */
    public function onTask($server, Task $task)
    {
        foreach ($task->data['f'] as $fd) {
            $server->push($fd, $task->data['d']);
        }
    }

    /**
     * 主节点消息通知
     *
     * @param $data
     */
    public function masterNotify($data)
    {
        $this->swoole->task($data);
    }
}