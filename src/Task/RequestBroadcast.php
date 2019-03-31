<?php
namespace Bee\Websocket\Task;

use Bee\Websocket\Slave\Bridge;
use Bee\Websocket\SlaveTask;
use Swoole\WebSocket\Server;

/**
 * 发送广播请求
 *
 * @package Bee\Websocket\Task
 */
class RequestBroadcast extends SlaveTask
{
    /**
     * 执行业务
     *
     * @param Server $server
     * @param Bridge $bridge
     * @param array $params
     * @throws \Bee\Websocket\Exception
     */
    public function handle(Server $server, Bridge $bridge, array $params = [])
    {
        foreach ($params as $param) {
            $bridge->broadcast($param['u'], $param['d']);
        }
    }
}
