<?php
namespace Bee\Websocket;

use Bee\Websocket\Slave\Bridge;
use Swoole\WebSocket\Server;

/**
 * SlaveTask
 *
 * @package Bee\Websocket
 */
abstract class SlaveTask
{
    /**
     * 执行业务
     *
     * @param Server $server
     * @param Bridge $bridge
     * @param array $params
     */
    abstract public function handle(Server $server, $bridge, array $params = []);
}