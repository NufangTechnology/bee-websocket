<?php
namespace Bee\Websocket;

abstract class Task
{
    /**
     * 执行业务
     *
     * @param \Swoole\WebSocket\Server $server
     * @param array $params
     */
    abstract public function handle($server, $params = []);
}