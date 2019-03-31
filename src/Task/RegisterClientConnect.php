<?php
namespace Bee\Websocket\Task;

use Bee\Websocket\MasterTask;
use Swoole\Server;
use Bee\Websocket\Master\SlavePool;
use Bee\Websocket\Master\ClientPool;

/**
 * 注册客户端连接
 *
 * @package Bee\Websocket\SlaveTask
 */
class RegisterClientConnect extends MasterTask
{
    /**
     * @param Server $server
     * @param array $params
     * @param SlavePool $slavePool
     * @param ClientPool $clientPool
     */
    public function handle(Server $server, array $params, SlavePool $slavePool, ClientPool $clientPool)
    {
        $clientPool->set($params['u'], $params['a']);
    }
}
