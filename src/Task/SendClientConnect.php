<?php
namespace Bee\Websocket\Task;

use Bee\Websocket\Slave\Bridge;
use Bee\Websocket\SlaveTask;
use Swoole\WebSocket\Server;

/**
 * 发送客户端连接信息至主节点
 *
 * @package Bee\Websocket\Task
 */
class SendClientConnect extends SlaveTask
{
    /**
     * 执行业务
     *
     * @param Server $server
     * @param Bridge $bridge
     * @param array $params
     * @throws \Bee\Websocket\Exception
     */
    public function handle(Server $server, $bridge, array $params = [])
    {
        $bridge->register($params);
    }
}
