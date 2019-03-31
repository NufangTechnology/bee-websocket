<?php
namespace Bee\Websocket\Task;

use Bee\Websocket\Slave\Bridge;
use Bee\Websocket\SlaveTask;
use \Swoole\WebSocket\Server;

/**
 * 想客户端推送消息体
 *
 * @package Bee\Websocket\Task
 */
class PushMessage extends SlaveTask
{
    /**
     * 执行业务
     *
     * @param Server $server
     * @param Bridge $bridge
     * @param array $params
     */
    public function handle(Server $server, Bridge $bridge, $params = [])
    {
        $fds  = $params['f'];
        $data = json_encode($params['d']);

        foreach ($fds as $fd) {
            $server->push($fd, $data);
        }
    }
}
