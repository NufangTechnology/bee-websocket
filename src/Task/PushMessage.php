<?php
namespace Bee\Websocket\Task;

use Bee\Websocket\Task;

/**
 * 想客户端推送消息体
 *
 * @package Bee\Websocket\Task
 */
class PushMessage extends Task
{
    /**
     * 执行业务
     *
     * @param \Swoole\WebSocket\Server $server
     * @param array $params
     */
    public function handle($server, $params = [])
    {
        $fds  = $params['fds'];
        $data = json_encode($params['data']);

        foreach ($fds as $fd) {
            $server->push($fd, $data);
        }
    }
}
