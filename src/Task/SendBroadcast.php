<?php
namespace Bee\Websocket\Task;

use Swoole\Server;
use Bee\Websocket\MasterTask;
use Bee\Websocket\Master\ClientPool;
use Bee\Websocket\Master\SlavePool;

/**
 * 向子节点发起发送广播指令
 *  - 通过 uuid 获取其所在的从节点 fd 和连接对象 fd
 *
 * @package Bee\Websocket\Task
 */
class SendBroadcast extends MasterTask
{
    /**
     * 执行业务
     *
     * @param Server $server
     * @param array $params
     * @param SlavePool $slavePool
     * @param ClientPool $clientPool
     */
    public function handle(Server $server, array $params, SlavePool $slavePool, ClientPool $clientPool)
    {
        $map = [];

        // 将相同自节点用户合并，减少消息通信量
        foreach ($params['u'] as $uuid) {
            // 获取客户端连接信息
            $client = $clientPool->get($uuid);
            if (empty($client)) {
                continue;
            }

            // 获取子节点连接信息
            $slave  = $slavePool->get($client['slave']);
            if (empty($slave)) {
                continue;
            }

            // 检查子节点连接是否有效
            // 无效则从对象池删除，降低内存使用
            // 当心用户连接进来时会自动重新注册
            if ($server->exist($slave['fd'])) {
                $map[$slave['fd']][] = $client['fd'];
            } else {
                $clientPool->del($uuid);
            }
        }

        // 给各个子节点发送广播指令
        foreach ($map as $slaveFd => $fds) {
            $server->send($slaveFd, serialize(['f' => $fds, 'd' => $params['d']]));
        }
    }
}
