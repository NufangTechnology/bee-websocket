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
    public function handle(Server $server, $bridge, $params = [])
    {
        $fds  = $params['f'];
        $data = json_encode($params['d']);

        foreach ($fds as $fd) {
            // 连接不存在，跳过
            // todo: 从映射移除
            if ($server->exist($fd)) {
                continue;
            }

            $server->push($fd, $data);
        }
    }
}
