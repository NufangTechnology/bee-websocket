<?php
namespace Bee\Websocket;

use Swoole\Server;
use Bee\Websocket\Master\ClientPool;
use Bee\Websocket\Master\SlavePool;

/**
 * MasterTask
 *
 * @package Bee\Websocket\Task
 */
abstract class MasterTask
{
    /**
     * 执行业务
     *
     * @param Server $server
     * @param array $params
     * @param SlavePool $slavePool
     * @param ClientPool $clientPool
     */
    abstract public function handle(Server $server, array $params, SlavePool $slavePool, ClientPool $clientPool);
}
